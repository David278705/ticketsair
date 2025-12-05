<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vuelo Recomendado del Día</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f3f4f6;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .greeting strong {
            color: #2563eb;
        }
        .intro-text {
            color: #4b5563;
            margin-bottom: 30px;
            font-size: 15px;
        }
        .flight-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px solid #bfdbfe;
        }
        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .city {
            text-align: center;
            flex: 1;
        }
        .city-name {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 5px;
        }
        .airport-code {
            font-size: 14px;
            color: #60a5fa;
            font-weight: 600;
        }
        .plane-icon {
            font-size: 30px;
            color: #3b82f6;
            margin: 0 15px;
        }
        .flight-details {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #6b7280;
            font-size: 14px;
        }
        .detail-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }
        .price-section {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .price-label {
            color: #92400e;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .price {
            font-size: 36px;
            font-weight: 700;
            color: #b45309;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .footer-text {
            color: #6b7280;
            font-size: 13px;
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .unsubscribe {
            color: #9ca3af;
            font-size: 12px;
            text-align: center;
            margin-top: 20px;
        }
        .unsubscribe a {
            color: #3b82f6;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .container {
                border-radius: 0;
            }
            .header {
                padding: 30px 20px;
            }
            .content {
                padding: 30px 20px;
            }
            .flight-route {
                flex-direction: column;
            }
            .plane-icon {
                transform: rotate(90deg);
                margin: 15px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✈️ Vuelo Recomendado del Día</h1>
            <p>{{ $originCity }} → {{ $destinationCity }}</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                ¡Hola <strong>{{ $user->first_name ?? $user->name }}</strong>! 👋
            </div>
            
            <p class="intro-text">
                Hemos seleccionado especialmente para ti este increíble vuelo. 
                ¡No dejes pasar esta oportunidad de viajar al mejor precio!
            </p>
            
            <div class="flight-card">
                <!-- Ruta del Vuelo -->
                <table style="width: 100%; margin-bottom: 20px;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="text-align: center; width: 40%;">
                            <div style="font-size: 24px; font-weight: 700; color: #1e3a8a;">
                                {{ $originCity }}
                            </div>
                        </td>
                        <td style="text-align: center; width: 20%;">
                            <div style="font-size: 30px; color: #3b82f6;">✈️</div>
                        </td>
                        <td style="text-align: center; width: 40%;">
                            <div style="font-size: 24px; font-weight: 700; color: #1e3a8a;">
                                {{ $destinationCity }}
                            </div>
                        </td>
                    </tr>
                </table>
                
                <div class="flight-details">
                    <div class="detail-row">
                        <span class="detail-label">📅 Fecha de Salida</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($flight->departure_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">🕐 Hora de Salida</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($flight->departure_at)->format('h:i A') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">🕑 Hora de Llegada</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($flight->arrival_at)->format('h:i A') }}</span>
                    </div>
                    @if($aircraftModel)
                    <div class="detail-row">
                        <span class="detail-label">✈️ Aeronave</span>
                        <span class="detail-value">{{ $aircraftModel }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">🎫 Vuelo</span>
                        <span class="detail-value">{{ $flight->code }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">💺 Asientos Disponibles</span>
                        <span class="detail-value">{{ $flight->capacity_economy + $flight->capacity_first }}</span>
                    </div>
                </div>
            </div>
            
            <div class="price-section">
                <div class="price-label">Precio desde</div>
                <div class="price">${{ number_format($flight->price_per_seat, 0, ',', '.') }} COP</div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $flightUrl }}" class="cta-button">
                    Comprar Este Vuelo
                </a>
            </div>
            
            <div class="footer-text">
                <p><strong>¿Por qué este vuelo?</strong></p>
                <p>Nuestro sistema selecciona los mejores vuelos basándose en disponibilidad, rutas populares y fechas próximas.</p>
                <p style="margin-top: 10px; font-size: 12px; color: #9ca3af;">
                    Al hacer clic, podrás ver todos los detalles y completar tu compra inmediatamente.
                </p>
            </div>
            
            <div class="unsubscribe">
                <p>
                    Si no deseas recibir estas recomendaciones, puedes 
                    <a href="{{ env('APP_URL') }}/profile">desactivarlas en tu perfil</a>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
