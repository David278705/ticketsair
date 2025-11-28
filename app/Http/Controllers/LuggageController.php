<?php

namespace App\Http\Controllers;

use App\Models\Luggage;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LuggageController extends Controller
{
    /**
     * Registrar equipaje para un ticket
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|exists:tickets,id',
            'type' => 'required|in:cabin,hold',
            'pieces' => 'required|integer|min:0|max:3',
            'extra_fee' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el ticket exista y pertenezca al usuario autenticado
        $ticket = Ticket::with('bookingPassenger.booking')->find($request->ticket_id);
        
        if (!$ticket || $ticket->bookingPassenger->booking->user_id !== auth()->id()) {
            return response()->json([
                'ok' => false,
                'message' => 'No tienes permiso para registrar equipaje en este ticket'
            ], 403);
        }

        // Validar límites según tipo
        if ($request->type === 'cabin' && $request->pieces > 1) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo se permite 1 pieza de equipaje de cabina'
            ], 422);
        }

        if ($request->type === 'hold' && $request->pieces > 3) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo se permiten hasta 3 piezas de equipaje facturado'
            ], 422);
        }

        // Eliminar equipaje previo si existe
        Luggage::where('ticket_id', $request->ticket_id)->delete();

        // Crear nuevo registro de equipaje
        $luggage = Luggage::create([
            'ticket_id' => $request->ticket_id,
            'type' => $request->type,
            'pieces' => $request->pieces,
            'extra_fee' => $request->extra_fee ?? 0
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Equipaje registrado exitosamente',
            'luggage' => $luggage
        ]);
    }

    /**
     * Obtener equipaje de un ticket
     */
    public function show($ticketId)
    {
        $ticket = Ticket::with(['bookingPassenger.booking', 'luggage'])->find($ticketId);

        if (!$ticket || $ticket->bookingPassenger->booking->user_id !== auth()->id()) {
            return response()->json([
                'ok' => false,
                'message' => 'Ticket no encontrado'
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'luggage' => $ticket->luggage
        ]);
    }

    /**
     * Actualizar equipaje
     */
    public function update(Request $request, $ticketId)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:cabin,hold',
            'pieces' => 'required|integer|min:0|max:3',
            'extra_fee' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = Ticket::with('bookingPassenger.booking')->find($ticketId);
        
        if (!$ticket || $ticket->bookingPassenger->booking->user_id !== auth()->id()) {
            return response()->json([
                'ok' => false,
                'message' => 'No tienes permiso para actualizar este equipaje'
            ], 403);
        }

        $luggage = Luggage::where('ticket_id', $ticketId)->first();

        if (!$luggage) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay equipaje registrado para este ticket'
            ], 404);
        }

        $luggage->update([
            'type' => $request->type,
            'pieces' => $request->pieces,
            'extra_fee' => $request->extra_fee ?? 0
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Equipaje actualizado exitosamente',
            'luggage' => $luggage
        ]);
    }

    /**
     * Eliminar equipaje
     */
    public function destroy($ticketId)
    {
        $ticket = Ticket::with('bookingPassenger.booking')->find($ticketId);
        
        if (!$ticket || $ticket->bookingPassenger->booking->user_id !== auth()->id()) {
            return response()->json([
                'ok' => false,
                'message' => 'No tienes permiso para eliminar este equipaje'
            ], 403);
        }

        Luggage::where('ticket_id', $ticketId)->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Equipaje eliminado exitosamente'
        ]);
    }
}
