<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación de Administrador - TicketsAir</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .welcome {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .credentials {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials h3 {
            color: #495057;
            margin-top: 0;
        }
        .credential-item {
            margin: 10px 0;
            font-family: monospace;
            background-color: #e9ecef;
            padding: 8px;
            border-radius: 3px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            opacity: 0.9;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✈️ TicketsAir</h1>
            <p>Sistema de Gestión de Vuelos</p>
        </div>

        <div class="welcome">
            <p>Hola <strong>{{ $user->name }}</strong>,</p>
            
            <p>Has sido invitado a formar parte del equipo administrativo de <strong>TicketsAir</strong>. Para acceder a tu cuenta, necesitarás iniciar sesión con las credenciales temporales que te proporcionamos.</p>
        </div>

        <div class="credentials">
            <h3>🔐 Credenciales de Acceso</h3>
            <div class="credential-item">
                <strong>Email:</strong> {{ $user->email }}
            </div>
            <div class="credential-item">
                <strong>Contraseña temporal:</strong> {{ $temporaryPassword }}
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/') }}" class="button">
                Iniciar Sesión
            </a>
        </div>

        <div class="warning">
            <strong>⚠️ Instrucciones importantes:</strong>
            <ul>
                <li><strong>Paso 1:</strong> Haz clic en "Iniciar Sesión" e ingresa tus credenciales</li>
                <li><strong>Paso 2:</strong> Serás redirigido automáticamente para completar tu registro</li>
                <li><strong>Paso 3:</strong> Completa tus datos personales y establece una nueva contraseña</li>
                <li>Las credenciales temporales son válidas por <strong>24 horas</strong></li>
                <li>Si no completas el registro en 24 horas, será necesario generar una nueva invitación</li>
            </ul>
        </div>

        <p>Una vez que hayas completado tu registro, tendrás acceso completo al panel administrativo donde podrás:</p>
        <ul>
            <li>Gestionar vuelos y horarios</li>
            <li>Administrar promociones</li>
            <li>Publicar noticias</li>
            <li>Gestionar mensajería con clientes</li>
        </ul>

        <div class="footer">
            <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactar al administrador principal.</p>
            <p><strong>Equipo TicketsAir</strong><br>
            Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>