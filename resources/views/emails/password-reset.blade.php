<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña - TicketsAir</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 16px;
        }
        .emoji {
            font-size: 28px;
        }
        .title {
            color: #1f2937;
            font-size: 28px;
            font-weight: bold;
            margin: 0 0 8px 0;
        }
        .subtitle {
            color: #6b7280;
            font-size: 16px;
            margin: 0;
        }
        .user-info {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
            border-left: 4px solid #3b82f6;
        }
        .user-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 18px;
        }
        .user-role {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }
        .role-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 8px;
        }
        .message {
            color: #4b5563;
            font-size: 16px;
            margin: 24px 0;
            line-height: 1.7;
        }
        .button-container {
            text-align: center;
            margin: 32px 0;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.4);
            transition: all 0.3s ease;
        }
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(59, 130, 246, 0.5);
        }
        .warning {
            background: #fef3cd;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
            color: #92400e;
        }
        .warning-title {
            font-weight: 600;
            margin-bottom: 8px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .contact-info {
            margin-top: 16px;
            color: #9ca3af;
            font-size: 12px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            .title {
                font-size: 24px;
            }
            .reset-button {
                padding: 14px 24px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <span class="emoji">✈️</span>
                TicketsAir
            </div>
            <h1 class="title">Recuperación de Contraseña</h1>
            <p class="subtitle">Solicitud de restablecimiento de contraseña</p>
        </div>

        <div class="user-info">
            <div class="user-name">{{ $user->first_name }} {{ $user->last_name }}</div>
            <div class="user-role">
                {{ $user->email }}
                <span class="role-badge">
                    @if($user->role->name === 'root')
                        Administrador Root
                    @elseif($user->role->name === 'admin')
                        Administrador
                    @else
                        Cliente
                    @endif
                </span>
            </div>
        </div>

        <div class="message">
            <p>Hola <strong>{{ $user->first_name }}</strong>,</p>
            
            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta de TicketsAir. Si fuiste tú quien realizó esta solicitud, haz clic en el botón de abajo para crear una nueva contraseña.</p>
            
            <p>Si no solicitaste este cambio, puedes ignorar este email de forma segura. Tu contraseña actual seguirá siendo válida.</p>
        </div>

        <div class="button-container">
            <a href="{{ $resetUrl }}" class="reset-button">
                Restablecer Mi Contraseña
            </a>
        </div>

        <div class="warning">
            <div class="warning-title">⚠️ Información Importante</div>
            <p>• Este enlace es válido por <strong>24 horas</strong> únicamente.</p>
            <p>• Una vez que uses este enlace, tu sesión actual será cerrada automáticamente.</p>
            <p>• Si el enlace no funciona, cópialo y pégalo directamente en tu navegador.</p>
        </div>

        <div class="message">
            <p><strong>Enlace alternativo:</strong></p>
            <p style="background: #f9fafb; padding: 12px; border-radius: 6px; word-break: break-all; font-family: monospace; font-size: 14px; border: 1px solid #e5e7eb;">
                {{ $resetUrl }}
            </p>
        </div>

        <div class="footer">
            <p><strong>¿Necesitas ayuda?</strong></p>
            <p>Si tienes problemas para restablecer tu contraseña, contáctanos.</p>
            
            <div class="contact-info">
                <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                <p>&copy; {{ date('Y') }} TicketsAir. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
