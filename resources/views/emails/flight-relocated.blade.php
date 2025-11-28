<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Vuelo</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-left: 4px solid #667eea;
        }
        .flight-info h3 {
            margin-top: 0;
            color: #667eea;
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
        .alert {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0;
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
        <h1>✈️ Cambio de Vuelo</h1>
        <p>Tu vuelo ha sido reubicado</p>
    </div>
    
    <div class="content">
        <p>Estimado(a) {{ $booking->user->name }},</p>
        
        <p>Lamentamos informarte que el vuelo <strong>{{ $originalFlight->code }}</strong> ha sido cancelado por razones operativas. Sin embargo, hemos reubicado automáticamente tu reserva en un vuelo alternativo.</p>
        
        <div class="flight-info">
            <h3>❌ Vuelo Original (Cancelado)</h3>
            <div class="info-row">
                <span class="label">Código:</span>
                <span>{{ $originalFlight->code }}</span>
            </div>
            <div class="info-row">
                <span class="label">Ruta:</span>
                <span>{{ $originalFlight->origin->name }} → {{ $originalFlight->destination->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Fecha original:</span>
                <span>{{ \Carbon\Carbon::parse($originalFlight->departure_at)->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        
        <div class="flight-info">
            <h3>✅ Nuevo Vuelo (Confirmado)</h3>
            <div class="info-row">
                <span class="label">Código:</span>
                <span>{{ $newFlight->code }}</span>
            </div>
            <div class="info-row">
                <span class="label">Número de vuelo:</span>
                <span>{{ $newFlight->flight_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Ruta:</span>
                <span>{{ $newFlight->origin->name }} → {{ $newFlight->destination->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Nueva fecha:</span>
                <span>{{ \Carbon\Carbon::parse($newFlight->departure_at)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Duración:</span>
                <span>{{ $newFlight->duration_minutes }} minutos</span>
            </div>
            <div class="info-row">
                <span class="label">Reserva:</span>
                <span>{{ $booking->booking_code }}</span>
            </div>
        </div>
        
        <div class="alert">
            <strong>⚠️ ¿No te conviene el nuevo horario?</strong>
            <p style="margin: 10px 0;">Si el nuevo vuelo no se ajusta a tus planes, puedes cancelar tu reserva desde la sección "Mis Viajes" y recibirás un <strong>reembolso completo</strong> a tu billetera.</p>
        </div>
        
        <p>Tus asientos han sido asignados automáticamente en el nuevo vuelo. Puedes verificar tu nueva reserva en la sección "Mis Viajes".</p>
        
        <p>Lamentamos las molestias ocasionadas y agradecemos tu comprensión.</p>
        
        <div class="footer">
            <p><strong>TicketsAir</strong></p>
            <p>Tu satisfacción es nuestra prioridad</p>
        </div>
    </div>
</body>
</html>
