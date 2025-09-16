<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Display a listing of messages for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $messages = Message::with(['from', 'to'])
            ->where(function ($query) use ($user) {
                $query->where('from_user_id', $user->id)
                      ->orWhere('to_user_id', $user->id);
            })
            ->when($request->conversation_with, function ($query, $conversationWith) use ($user) {
                return $query->where(function ($q) use ($user, $conversationWith) {
                    $q->where(function ($subQ) use ($user, $conversationWith) {
                        $subQ->where('from_user_id', $user->id)
                             ->where('to_user_id', $conversationWith);
                    })->orWhere(function ($subQ) use ($user, $conversationWith) {
                        $subQ->where('from_user_id', $conversationWith)
                             ->where('to_user_id', $user->id);
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }

    /**
     * Get messages for admin panel (admin can see all messages to/from admins)
     */
    public function adminMessages(Request $request)
    {
        $user = $request->user();
        
        // Solo admins y root pueden acceder
        if (!in_array($user->role->name, ['admin', 'root'])) {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $messages = Message::with(['from', 'to'])
            ->whereHas('to', function ($query) {
                $query->whereHas('role', function ($q) {
                    $q->whereIn('name', ['admin', 'root']);
                });
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'unread') {
                    return $query->where('is_read', false);
                } elseif ($status === 'read') {
                    return $query->where('is_read', true);
                }
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', '%' . $search . '%')
                      ->orWhere('body', 'like', '%' . $search . '%')
                      ->orWhereHas('from', function ($userQuery) use ($search) {
                          $userQuery->where('email', 'like', '%' . $search . '%')
                                   ->orWhere('first_name', 'like', '%' . $search . '%')
                                   ->orWhere('last_name', 'like', '%' . $search . '%');
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($messages);
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar que el usuario no se envíe mensajes a sí mismo
        if ($request->user()->id == $request->to_user_id) {
            return response()->json(['error' => 'Cannot send message to yourself'], 422);
        }

        $message = Message::create([
            'from_user_id' => $request->user()->id,
            'to_user_id' => $request->to_user_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'is_read' => false,
            'sent_at' => now()
        ]);

        $message->load(['from', 'to']);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }

    /**
     * Send message to admin (for clients)
     */
    public function sendToAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar un admin disponible (se puede mejorar con lógica de asignación)
        $admin = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$admin) {
            return response()->json(['error' => 'No administrators available at the moment'], 500);
        }

        $message = Message::create([
            'from_user_id' => $request->user()->id,
            'to_user_id' => $admin->id,
            'subject' => $request->subject,
            'body' => $request->body,
            'is_read' => false,
            'sent_at' => now()
        ]);

        $message->load(['from', 'to']);

        return response()->json([
            'message' => 'Message sent to administrator successfully',
            'data' => $message
        ], 201);
    }

    /**
     * Reply to a message (for admins)
     */
    public function reply(Request $request, Message $originalMessage)
    {
        $user = $request->user();
        
        // Solo admins y root pueden responder mensajes
        if (!in_array($user->role->name, ['admin', 'root'])) {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        // Verificar que el mensaje original fue enviado al admin actual o a cualquier admin
        if ($originalMessage->to_user_id !== $user->id && 
            !in_array($originalMessage->to->role->name, ['admin', 'root'])) {
            return response()->json(['error' => 'Cannot reply to this message'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Crear la respuesta
        $reply = Message::create([
            'from_user_id' => $user->id,
            'to_user_id' => $originalMessage->from_user_id,
            'subject' => 'Re: ' . $originalMessage->subject,
            'body' => $request->body,
            'is_read' => false,
            'sent_at' => now()
        ]);

        // Marcar el mensaje original como leído
        $originalMessage->update(['is_read' => true]);

        $reply->load(['from', 'to']);

        return response()->json([
            'message' => 'Reply sent successfully',
            'data' => $reply
        ], 201);
    }

    /**
     * Display the specified message.
     */
    public function show(Request $request, Message $message)
    {
        $user = $request->user();
        
        // Verificar que el usuario puede ver este mensaje
        if ($message->from_user_id !== $user->id && $message->to_user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized to view this message'], 403);
        }

        // Marcar como leído si es el destinatario
        if ($message->to_user_id === $user->id && !$message->is_read) {
            $message->update(['is_read' => true]);
        }

        $message->load(['from', 'to']);

        return response()->json($message);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, Message $message)
    {
        $user = $request->user();
        
        // Solo el destinatario puede marcar como leído
        if ($message->to_user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized to mark this message as read'], 403);
        }

        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Message marked as read']);
    }

    /**
     * Get conversation between two users
     */
    public function getConversation(Request $request, User $otherUser)
    {
        $user = $request->user();
        
        $messages = Message::with(['from', 'to'])
            ->where(function ($query) use ($user, $otherUser) {
                $query->where(function ($q) use ($user, $otherUser) {
                    $q->where('from_user_id', $user->id)
                      ->where('to_user_id', $otherUser->id);
                })->orWhere(function ($q) use ($user, $otherUser) {
                    $q->where('from_user_id', $otherUser->id)
                      ->where('to_user_id', $user->id);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Marcar mensajes como leídos si el usuario actual es el destinatario
        Message::where('from_user_id', $otherUser->id)
               ->where('to_user_id', $user->id)
               ->where('is_read', false)
               ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        
        $count = Message::where('to_user_id', $user->id)
                       ->where('is_read', false)
                       ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Remove the specified message from storage.
     */
    public function destroy(Request $request, Message $message)
    {
        $user = $request->user();
        
        // Solo el remitente puede eliminar su mensaje
        if ($message->from_user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized to delete this message'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }
}
