<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class CheckinController extends Controller
{
 public function fast(\App\Http\Requests\CheckinStoreRequest $request)
{
    $data = $request->validated();

    // Buscar por ticket_code (tras compra) o por DNI
    $ticket = null;
    if (!empty($data['ticket_code'])) {
        $ticket = \App\Models\Ticket::where('ticket_code', $data['ticket_code'])->first();
    } elseif (!empty($data['dni'])) {
        $ticket = \App\Models\Ticket::whereHas('passenger', fn($q)=>$q->where('dni',$data['dni']))->first();
    }
    if (!$ticket) return response()->json(['error'=>'not_found'],404);

    if ($ticket->status === 'checked_in') return response()->json(['error'=>'already_checked_in'],422);

    $ticket->update(['status'=>'checked_in']);
    $check = \App\Models\Checkin::create([
        'ticket_id'=>$ticket->id,
        'checked_in_at'=>now(),
        // TODO: generar y adjuntar PDF pasabordo
    ]);

     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.boarding-pass', [
        'ticket'=>$ticket->load('passenger','booking.flight.origin','booking.flight.destination'),
        ]);
        $path = 'boarding-passes/'. Str::uuid().'.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        $check->update(['boarding_pass_pdf_path'=>$path]);

    // Equipaje adicional (si lo decides aquí)
    // \App\Models\Luggage::create([...]);

    return response()->json(['ok'=>true, 'boarding_pass'=>$check->boarding_pass_pdf_path]);
}

}
