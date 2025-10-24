<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pasabordo - {{ $ticket->ticket_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #1e293b;
            padding: 20px;
        }
        
        .boarding-pass {
            border: 2px solid #0ea5e9;
            border-radius: 12px;
            overflow: hidden;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.95;
        }
        
        .content {
            padding: 30px;
        }
        
        .flight-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .flight-route {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .city {
            display: table-cell;
            width: 35%;
            text-align: center;
            vertical-align: middle;
        }
        
        .arrow {
            display: table-cell;
            width: 30%;
            text-align: center;
            vertical-align: middle;
            padding: 0 20px;
        }
        
        .arrow-line {
            height: 2px;
            background: #cbd5e1;
            position: relative;
            margin: 10px 0;
        }
        
        .arrow-line::after {
            content: '';
            position: absolute;
            right: 0;
            top: -4px;
            width: 0;
            height: 0;
            border-left: 10px solid #cbd5e1;
            border-top: 5px solid transparent;
            border-bottom: 5px solid transparent;
        }
        
        .city-code {
            font-size: 32px;
            font-weight: bold;
            color: #0ea5e9;
            margin-bottom: 5px;
        }
        
        .city-name {
            font-size: 12px;
            color: #64748b;
        }
        
        .flight-number {
            text-align: center;
            color: #64748b;
            font-size: 11px;
            margin-top: 10px;
        }
        
        .details-grid {
            display: table;
            width: 100%;
            border-top: 2px dashed #e2e8f0;
            padding-top: 25px;
        }
        
        .detail-item {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border-right: 1px solid #e2e8f0;
        }
        
        .detail-item:last-child {
            border-right: none;
        }
        
        .detail-label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
        }
        
        .passenger-info {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        
        .passenger-grid {
            display: table;
            width: 100%;
        }
        
        .passenger-item {
            display: table-cell;
            width: 50%;
            padding: 10px;
        }
        
        .barcode {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px dashed #e2e8f0;
        }
        
        .barcode-image {
            height: 60px;
            margin: 10px 0;
        }
        
        .ticket-code {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 2px;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            margin-top: 20px;
        }
        
        .important-notice {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-top: 20px;
            font-size: 11px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="boarding-pass">
        <!-- Header -->
        <div class="header">
            <h1>✈️ TICKETSAIR</h1>
            <p>Pasabordo Electrónico</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Flight Route -->
            <div class="flight-route">
                <div class="city">
                    <div class="city-code">{{ strtoupper(substr($ticket->booking->flight->origin->name, 0, 3)) }}</div>
                    <div class="city-name">{{ $ticket->booking->flight->origin->name }}</div>
                </div>
                <div class="arrow">
                    <div class="arrow-line"></div>
                    <div class="flight-number">Vuelo {{ $ticket->booking->flight->code }}</div>
                </div>
                <div class="city">
                    <div class="city-code">{{ strtoupper(substr($ticket->booking->flight->destination->name, 0, 3)) }}</div>
                    <div class="city-name">{{ $ticket->booking->flight->destination->name }}</div>
                </div>
            </div>
            
            <!-- Flight Details -->
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Fecha</div>
                    <div class="detail-value">{{ $ticket->booking->flight->departure_at->format('d/m/Y') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Hora</div>
                    <div class="detail-value">{{ $ticket->booking->flight->departure_at->format('H:i') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Duración</div>
                    <div class="detail-value">{{ $ticket->booking->flight->duration_minutes }}min</div>
                </div>
            </div>
            
            <!-- Passenger Info -->
            <div class="passenger-info">
                <div class="passenger-grid">
                    <div class="passenger-item">
                        <div class="detail-label">Pasajero</div>
                        <div class="detail-value" style="font-size: 16px;">
                            {{ strtoupper($ticket->passenger->first_name . ' ' . $ticket->passenger->last_name) }}
                        </div>
                    </div>
                    <div class="passenger-item">
                        <div class="detail-label">Documento</div>
                        <div class="detail-value" style="font-size: 16px;">{{ $ticket->passenger->dni }}</div>
                    </div>
                </div>
                <div class="passenger-grid" style="margin-top: 15px;">
                    <div class="passenger-item">
                        <div class="detail-label">Asiento</div>
                        <div class="detail-value" style="font-size: 16px;">
                            {{ $ticket->passenger->seat ? $ticket->passenger->seat->number : 'Por asignar' }}
                        </div>
                    </div>
                    <div class="passenger-item">
                        <div class="detail-label">Clase</div>
                        <div class="detail-value" style="font-size: 16px;">
                            {{ $ticket->passenger->class === 'first' ? 'Primera Clase' : 'Económica' }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Barcode -->
            <div class="barcode">
                <div style="font-size: 11px; color: #64748b; margin-bottom: 10px;">
                    Código de Tiquete
                </div>
                <div class="barcode-image">
                    <svg width="300" height="60" style="margin: 0 auto; display: block;">
                        @for ($i = 0; $i < 40; $i++)
                            <rect x="{{ $i * 7.5 }}" y="0" width="{{ rand(2, 5) }}" height="60" fill="#1e293b"/>
                        @endfor
                    </svg>
                </div>
                <div class="ticket-code">{{ $ticket->ticket_code }}</div>
            </div>
            
            <!-- Important Notice -->
            <div class="important-notice">
                <strong> IMPORTANTE:</strong><br>
                • Presentarse en el aeropuerto con 2 horas de antelación<br>
                • Portar documento de identidad original<br>
                • Este pasabordo es válido solo para el vuelo indicado<br>
                • Código de Reserva: <strong>{{ $ticket->booking->reservation_code }}</strong>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            Generado el {{ now()->format('d/m/Y H:i') }} | TicketsAir - Tu compañero de viaje
        </div>
    </div>
</body>
</html>
