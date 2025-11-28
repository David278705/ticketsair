<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vuelo Cancelado - Reembolso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .flight-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ef4444;
        }
        .flight-info h3 {
            margin-top: 0;
            color: #ef4444;
        }
        .info-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #64748b;
            display: inline-block;
            width: 150px;
        }
        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✈️ Vuelo Cancelado</h1>
        <p>Reembolso procesado</p>
    </div>
    
    <div class="content">
        <p>Estimado(a) {{ $booking->user->name }},</p>
        
        <p>Lamentamos informarte que el vuelo <strong>{{ $flight->code }}</strong> ha sido cancelado por razones operativas.</p>
        
        <div class="flight-info">
            <h3>❌ Vuelo Cancelado</h3>
            <div class="info-row">
                <span class="label">Código:</span>
                <span>{{ $flight->code }}</span>
            </div>
            <div class="info-row">
                <span class="label">Número de vuelo:</span>
                <span>{{ $flight->flight_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Ruta:</span>
                <span>{{ $flight->origin->name }} → {{ $flight->destination->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Fecha programada:</span>
                <span>{{ \Carbon\Carbon::parse($flight->departure_at)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Reserva:</span>
                <span>{{ $booking->booking_code }}</span>
            </div>
        </div>
        
        <div class="success-box">
            <h3 style="margin-top: 0; color: #10b981;">✅ Reembolso Procesado</h3>
            <p style="margin: 10px 0;">Hemos procesado el reembolso completo de tu compra automáticamente.</p>
            <div class="info-row">
                <span class="label">Monto reembolsado:</span>
                <span style="font-size: 18px; font-weight: bold; color: #10b981;">${{ number_format($booking->total_amount, 0, ',', '.') }} COP</span>
            </div>
            <p style="margin: 10px 0; font-size: 14px;">El dinero ha sido devuelto a tu billetera y está disponible de inmediato para futuras compras.</p>
        </div>
        
        <p>Lamentamos profundamente las molestias ocasionadas. Esperamos poder servirte en un futuro viaje.</p>
        
        <p>Si tienes alguna pregunta o necesitas asistencia, no dudes en contactarnos.</p>
        
        <div class="footer">
            <p><strong>TicketsAir</strong></p>
            <p>Gracias por tu comprensión</p>
        </div>
    </div>
</body>
</html>
