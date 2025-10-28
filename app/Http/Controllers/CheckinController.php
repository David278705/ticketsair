<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    // Validar tiempo de check-in según tipo de vuelo
    $booking = $ticket->booking;
    $flight = $booking->flight;
    $now = now();
    $hoursBeforeFlight = $now->diffInHours($flight->departure_at, false);
    
    // Nacional: 24 horas antes, Internacional: 48 horas antes
    $requiredHours = $flight->is_international ? 48 : 24;
    
    if ($hoursBeforeFlight > $requiredHours) {
        return response()->json([
            'error' => 'too_early',
            'message' => "El check-in estará disponible {$requiredHours} horas antes del vuelo (" . $flight->departure_at->subHours($requiredHours)->format('d/m/Y H:i') . ")"
        ], 422);
    }
    
    if ($hoursBeforeFlight < 0) {
        return response()->json([
            'error' => 'flight_departed',
            'message' => 'El vuelo ya ha partido'
        ], 422);
    }

    $ticket->update(['status'=>'checked_in']);
    $check = \App\Models\Checkin::create([
        'ticket_id'=>$ticket->id,
        'checked_in_at'=>now(),
    ]);

    // Generar PDF del pasabordo INDIVIDUAL para este pasajero
    try {
        $ticketData = $ticket->load('passenger.seat', 'booking.flight.origin', 'booking.flight.destination', 'booking.flight.aircraft', 'booking.passengers.seat');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.boarding-pass', [
            'ticket' => $ticketData,
            'booking' => $ticketData->booking, // Incluir booking para mostrar todos los pasajeros
        ])->setPaper('a4', 'portrait');
        
        $path = 'boarding-passes/'. Str::uuid().'.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        
        // Guardar path en checkin Y en ticket
        $check->update(['boarding_pass_pdf_path' => $path]);
        $ticket->update(['boarding_pass_pdf_path' => $path]);
        
        // Enviar email SOLO al pasajero de este ticket
        $passenger = $ticket->passenger;
        if ($passenger->email) {
            try {
                Mail::to($passenger->email)->send(
                    new \App\Mail\BoardingPassMail($ticketData, $path)
                );
                Log::info("Pasabordo enviado a: {$passenger->email} para ticket {$ticket->ticket_code}");
            } catch (\Exception $e) {
                Log::error("Error enviando pasabordo a {$passenger->email}: " . $e->getMessage());
            }
        }
        
    } catch (\Exception $e) {
        Log::error('Error generando PDF de pasabordo: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
    }

    return response()->json([
        'ok' => true, 
        'boarding_pass' => $check->boarding_pass_pdf_path ?? null,
        'ticket' => $ticket->fresh()
    ]);
}

}
