<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeatChangeRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    public function index(\App\Models\Flight $flight){
        return \App\Models\Seat::where('flight_id',$flight->id)
            ->orderBy('number')
            ->get(['id','number','class','status']);
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
