<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ForumController extends Controller
{
    /**
     * Get public forum thread and messages
     * Supports incremental loading via since_message_id parameter
     */
    public function getPublicForum(Request $request)
    {
        $sinceMessageId = $request->query('since_message_id');
        
        $thread = Thread::public()->first();

        // Si no existe el hilo público, crearlo
        if (!$thread) {
            $thread = Thread::create([
                'type' => Thread::TYPE_PUBLIC,
                'title' => 'Foro Público',
                'status' => Thread::STATUS_OPEN
            ]);
        }

        // Si se proporciona since_message_id, solo traer mensajes nuevos
        if ($sinceMessageId) {
            $newMessages = Message::where('thread_id', $thread->id)
                ->where('id', '>', $sinceMessageId)
                ->with(['from.role', 'to'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $thread->id,
                    'new_messages' => $newMessages,
                    'count' => $newMessages->count()
                ]
            ]);
        }

        // Si no hay since_message_id, traer el thread completo
        $thread->load(['messages.from.role', 'messages.to']);

        return response()->json([
            'status' => 'success',
            'data' => $thread
        ]);
    }

    /**
     * Get private thread for authenticated user
     * Supports incremental loading via since_message_id parameter
     */
    public function getPrivateThread(Request $request)
    {
        $user = $request->user();
        $sinceMessageId = $request->query('since_message_id');

        $thread = Thread::private()
            ->forUser($user->id)
            ->first();

        if (!$thread) {
            return response()->json([
                'status' => 'success',
                'data' => null,
                'has_thread' => false
            ]);
        }

        // Si se proporciona since_message_id, solo traer mensajes nuevos
        if ($sinceMessageId) {
            $newMessages = Message::where('thread_id', $thread->id)
                ->where('id', '>', $sinceMessageId)
                ->with(['from.role', 'to'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Marcar nuevos mensajes como leídos si son para el usuario
            if ($newMessages->count() > 0) {
                Message::where('thread_id', $thread->id)
                    ->where('id', '>', $sinceMessageId)
                    ->where('to_user_id', $user->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $thread->id,
                    'new_messages' => $newMessages,
                    'count' => $newMessages->count()
                ],
                'has_thread' => true
            ]);
        }

        // Si no hay since_message_id, traer el thread completo
        $thread->load(['messages.from.role', 'messages.to']);

        return response()->json([
            'status' => 'success',
            'data' => $thread,
            'has_thread' => true
        ]);
    }

    /**
     * Create private thread for user
     */
    public function createPrivateThread(Request $request)
    {
        $user = $request->user();
        
        // Solo los clientes pueden crear hilos privados
        if ($user->role->name !== 'client') {
            return response()->json([
                'status' => 'error',
                'message' => 'Solo los clientes pueden iniciar conversaciones privadas'
            ], 403);
        }

        // Verificar si ya tiene un hilo privado
        $existingThread = Thread::private()
            ->forUser($user->id)
            ->first();

        if ($existingThread) {
            return response()->json([
                'status' => 'success',
                'data' => $existingThread,
                'message' => 'Thread already exists'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'first_message' => 'required|string|max:2000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Crear el hilo privado
            $thread = Thread::create([
                'user_id' => $user->id,
                'type' => Thread::TYPE_PRIVATE,
                'title' => $request->title ?: 'Soporte - ' . $user->first_name,
                'status' => Thread::STATUS_OPEN,
                'last_message_at' => now()
            ]);

            // Crear el primer mensaje
            $message = Message::create([
                'thread_id' => $thread->id,
                'from_user_id' => $user->id,
                'to_user_id' => null, // Para admins
                'body' => $request->first_message,
                'is_read' => false
            ]);

            $thread->load(['messages.from.role', 'messages.to']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $thread,
                'message' => 'Private thread created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating thread: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Post message to a thread
     */
    public function postMessage(Request $request, Thread $thread)
    {
        $user = $request->user();
        
        // Root no puede enviar mensajes (solo supervisar)
        if ($user->role->name === 'root') {
            return response()->json([
                'status' => 'error',
                'message' => 'Los usuarios root solo pueden supervisar el foro, no enviar mensajes'
            ], 403);
        }

        // Validar permisos
        if ($thread->type === Thread::TYPE_PRIVATE) {
            // Usuario debe ser el dueño o un admin
            $isOwner = $thread->user_id === $user->id;
            $isAdmin = $user->role->name === 'admin';
            
            if (!$isOwner && !$isAdmin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:2000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Determinar destinatario
        $toUserId = null;
        if ($thread->type === Thread::TYPE_PRIVATE) {
            // Si es admin, enviar al usuario del thread
            if ($user->role->name === 'admin') {
                $toUserId = $thread->user_id;
            }
            // Si es usuario, enviar a admins (null)
        }

        $message = Message::create([
            'thread_id' => $thread->id,
            'from_user_id' => $user->id,
            'to_user_id' => $toUserId,
            'body' => $request->body,
            'is_read' => false
        ]);

        // Actualizar last_message_at del thread
        $thread->update(['last_message_at' => now()]);

        $message->load(['from.role', 'to']);

        // TODO: Broadcast event para tiempo real
        // broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'success',
            'data' => $message
        ], 201);
    }

    /**
     * Get all private threads (for admins)
     */
    public function getPrivateThreadsForAdmin(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role->name, ['admin', 'root'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $threads = Thread::private()
            ->with(['user', 'latestMessage.from'])
            ->withCount([
                'messages as unread_count' => function ($query) {
                    $query->where('to_user_id', null)
                        ->where('is_read', false);
                }
            ])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $threads
        ]);
    }

    /**
     * Get specific thread (for admins or thread owner)
     * Supports incremental loading via since_message_id parameter
     */
    public function getThread(Request $request, Thread $thread)
    {
        $user = $request->user();
        $sinceMessageId = $request->query('since_message_id');

        // Validar permisos
        if ($thread->type === Thread::TYPE_PRIVATE) {
            $isOwner = $thread->user_id === $user->id;
            $isAdmin = in_array($user->role->name, ['admin', 'root']);
            
            if (!$isOwner && !$isAdmin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        // Si se proporciona since_message_id, solo traer mensajes nuevos
        if ($sinceMessageId) {
            $newMessages = Message::where('thread_id', $thread->id)
                ->where('id', '>', $sinceMessageId)
                ->with(['from.role', 'to'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Marcar nuevos mensajes como leídos
            if ($newMessages->count() > 0 && $thread->type === Thread::TYPE_PRIVATE) {
                if (in_array($user->role->name, ['admin', 'root'])) {
                    Message::where('thread_id', $thread->id)
                        ->where('id', '>', $sinceMessageId)
                        ->where('from_user_id', $thread->user_id)
                        ->where('is_read', false)
                        ->update(['is_read' => true]);
                } else {
                    Message::where('thread_id', $thread->id)
                        ->where('id', '>', $sinceMessageId)
                        ->where('to_user_id', $user->id)
                        ->where('is_read', false)
                        ->update(['is_read' => true]);
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $thread->id,
                    'new_messages' => $newMessages,
                    'count' => $newMessages->count()
                ]
            ]);
        }

        // Si no hay since_message_id, traer el thread completo
        $thread->load(['messages.from.role', 'messages.to', 'user']);

        // Marcar mensajes como leídos
        if ($thread->type === Thread::TYPE_PRIVATE) {
            if (in_array($user->role->name, ['admin', 'root'])) {
                // Admin marca como leídos los mensajes del usuario
                Message::where('thread_id', $thread->id)
                    ->where('from_user_id', $thread->user_id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            } else {
                // Usuario marca como leídos los mensajes de admin
                Message::where('thread_id', $thread->id)
                    ->where('to_user_id', $user->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $thread
        ]);
    }

    /**
     * Mark thread messages as read
     */
    public function markThreadAsRead(Request $request, Thread $thread)
    {
        $user = $request->user();

        if ($thread->type === Thread::TYPE_PRIVATE) {
            if (in_array($user->role->name, ['admin', 'root'])) {
                // Admin marca mensajes del usuario como leídos
                Message::where('thread_id', $thread->id)
                    ->where('from_user_id', $thread->user_id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            } else {
                // Usuario marca mensajes de admin como leídos
                Message::where('thread_id', $thread->id)
                    ->where('to_user_id', $user->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Messages marked as read'
        ]);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();

        $count = 0;

        if (in_array($user->role->name, ['admin', 'root'])) {
            // Contar mensajes sin leer en hilos privados
            $count = Message::whereHas('thread', function ($query) {
                $query->where('type', Thread::TYPE_PRIVATE);
            })
            ->where('to_user_id', null)
            ->where('is_read', false)
            ->count();
        } else {
            // Contar mensajes sin leer dirigidos al usuario
            $count = Message::where('to_user_id', $user->id)
                ->where('is_read', false)
                ->count();
        }

        return response()->json([
            'status' => 'success',
            'unread_count' => $count
        ]);
    }

    /**
     * Close thread (admin only)
     */
    public function closeThread(Request $request, Thread $thread)
    {
        $user = $request->user();

        if (!in_array($user->role->name, ['admin', 'root'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $thread->update(['status' => Thread::STATUS_CLOSED]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thread closed successfully'
        ]);
    }
}
