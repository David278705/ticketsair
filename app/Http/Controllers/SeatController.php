<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeatChangeRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    public function index(\App\Models\Flight $flight, Request $request){
        $seats = \App\Models\Seat::where('flight_id', $flight->id);
        
        // Filtrar por clase si se especifica
        if ($request->has('class')) {
            $seats->where('class', $request->class);
        }
        
        $seats = $seats->orderBy('number')
            ->get(['id', 'number', 'class', 'status']);
        
        // Obtener el seat_id actual del pasajero si se proporciona
        $currentSeatId = $request->get('current_seat_id');
        
        // Agregar campos calculados para el grid de asientos
        return $seats->map(function ($seat) use ($currentSeatId) {
            // Calcular fila y columna basado en el número
            // Asumiendo 6 asientos por fila (A-F)
            $row = ceil($seat->number / 6);
            $col = chr(65 + (($seat->number - 1) % 6)); // A, B, C, D, E, F
            
            // El asiento está disponible si:
            // 1. Su status es 'available', O
            // 2. Es el asiento actual del pasajero (puede re-seleccionarlo)
            $isAvailable = $seat->status === 'available' || 
                          ($currentSeatId && $seat->id == $currentSeatId);
            
            return [
                'id' => $seat->id,
                'number' => $seat->number,
                'row_number' => $row,
                'column' => $col,
                'seat_number' => $row . $col,
                'class' => $seat->class,
                'status' => $seat->status,
                'available' => $isAvailable
            ];
        });
    }

    public function change(SeatChangeRequest $request)
{
    $data = $request->validated();

    $bp = \App\Models\BookingPassenger::with(['booking.flight','seat'])
          ->findOrFail($data['booking_passenger_id']);

    // No más de 1 cambio y no después del check-in
    if ($bp->seat_changed_once) return response()->json(['error'=>'already_changed_once'],422);
    $ticket = $bp->ticket;
    if ($ticket && $ticket->status === 'checked_in') {
        return response()->json(['error'=>'cannot_change_after_checkin'],422);
    }

    if ($bp->booking->flight->departure_at->isPast()) {
    return response()->json(['error'=>'flight_already_departed'],422);
    }


    $to = \App\Models\Seat::findOrFail($data['to_seat_id']);
    if ($to->flight_id !== $bp->booking->flight_id || $to->status !== 'available' || $to->class !== $bp->class) {
        return response()->json(['error'=>'invalid_target_seat'],422);
    }

    DB::transaction(function () use ($bp, $to) {
        if ($bp->seat) $bp->seat->update(['status'=>'available']);
        $to->update(['status'=>'assigned']);
        $bp->update(['seat_id'=>$to->id, 'seat_changed_once'=>true]);
        \App\Models\SeatChange::create([
            'booking_passenger_id'=>$bp->id,
            'from_seat_id'=>$bp->seat?->id,
            'to_seat_id'=>$to->id,
            'changed_at'=>now(),
        ]);
    });



    return response()->json(['ok'=>true]);
}

}
