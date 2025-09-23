# Documentación - Sistema de Invitación de Administradores

## Resumen de Cambios

Se ha implementado un sistema de invitación por email para la creación de administradores, simplificando el proceso inicial y añadiendo un flujo de registro por pasos.

## Flujo del Nuevo Sistema

### 1. **Root invita administrador**
- Solo solicita: `email` y `nombre completo`
- Sistema genera automáticamente:
  - Contraseña temporal (12 caracteres aleatorios)
  - Token de verificación (60 caracteres)
  - Fecha de expiración (24 horas)

### 2. **Envío de email automático**
- Email HTML con diseño profesional
- Incluye credenciales temporales
- Link para completar registro
- Información sobre expiración y próximos pasos

### 3. **Administrador completa registro**
- Verifica token y email
- Completa datos faltantes: DNI, fecha nacimiento, etc.
- Define su contraseña definitiva
- Activa su cuenta para uso inmediato

## Archivos Modificados

### **Backend**

#### 1. `/app/Http/Requests/CreateAdminRequest.php`
```php
// Campos requeridos reducidos a:
'email' => 'required|email|unique:users,email',
'full_name' => 'required|string|max:255',
```

#### 2. `/app/Http/Controllers/Admin/UserAdminController.php`
- **Método `createAdmin()`** completamente rediseñado
- Genera contraseña temporal y token
- Divide nombre completo en first_name/last_name
- Envía email automáticamente
- Usuario creado con `registration_completed: false`

#### 3. `/app/Mail/AdminInvitationMail.php` (NUEVO)
- Mailable para envío de invitaciones
- Recibe usuario, contraseña temporal y URL de completar

#### 4. `/resources/views/emails/admin-invitation.blade.php` (NUEVO)
- Email HTML responsivo con diseño profesional
- Incluye credenciales y link de activación
- Información clara sobre el proceso

#### 5. `/app/Http/Controllers/AdminRegistrationController.php` (NUEVO)
- **`verifyToken()`**: Verifica validez del token y email
- **`completeRegistration()`**: Procesa el registro completo

#### 6. **Migración**: `add_admin_registration_fields_to_users_table`
```php
$table->string('temp_password_token')->nullable();
$table->timestamp('temp_password_expires_at')->nullable(); 
$table->boolean('registration_completed')->default(true);
```

#### 7. `/app/Models/User.php`
- Agregados nuevos campos en `$fillable` y `$casts`
- Soporte para campos de registro temporal

### **Frontend**

#### 8. `/resources/js/components/admin/CreateAdminModal.vue`
- **Formulario simplificado**: Solo email y nombre completo
- **Nueva información**: Explica el proceso post-creación
- **Botón actualizado**: "Enviar Invitación" en lugar de "Crear"
- **Mensaje de éxito**: Confirmación de envío de email

### **Rutas API**

#### 9. `/routes/api.php`
```php
// Nuevas rutas públicas para completar registro
Route::post('/verify-admin-token', [AdminRegistrationController::class, 'verifyToken']);
Route::post('/complete-admin-registration', [AdminRegistrationController::class, 'completeRegistration']);
```

## Nuevas APIs

### **Verificar Token** 
```http
POST /api/verify-admin-token
Content-Type: application/json

{
  "token": "token_de_60_caracteres",
  "email": "admin@example.com"
}
```

**Respuesta exitosa:**
```json
{
  "status": "success",
  "message": "Token válido",
  "data": {
    "email": "admin@example.com",
    "name": "Juan Pérez",
    "token_expires_at": "2025-09-24T02:14:23.000000Z"
  }
}
```

### **Completar Registro**
```http
POST /api/complete-admin-registration
Content-Type: application/json

{
  "token": "token_de_60_caracteres",
  "email": "admin@example.com",
  "dni": "12345678",
  "birth_date": "1990-05-15",
  "gender": "M",
  "billing_address": "Calle Example 123",
  "username": "admin_juan",
  "new_password": "mi_password_segura",
  "new_password_confirmation": "mi_password_segura"
}
```

## Validaciones del Sistema

### **Token de Seguridad**
- ✅ Token único de 60 caracteres
- ✅ Expiración de 24 horas
- ✅ Validación de email asociado
- ✅ Usuario debe estar en estado `registration_completed: false`

### **Campos Obligatorios para Completar**
- `dni`: Único en el sistema
- `birth_date`: Anterior a hoy
- `new_password`: Mínimo 8 caracteres + confirmación
- `gender`, `username`, `billing_address`: Opcionales

## Estados del Usuario Administrador

### **Estado 1: Invitado**
```php
'registration_completed' => false,
'temp_password_token' => 'token_60_chars',
'temp_password_expires_at' => '2025-09-24 02:14:23',
'password' => 'hash_temporal',
'is_active' => null // Sin definir
```

### **Estado 2: Registro Completado**
```php
'registration_completed' => true,
'temp_password_token' => null,
'temp_password_expires_at' => null, 
'password' => 'hash_definitivo',
'is_active' => true
```

## Beneficios del Sistema

### **Para el Root**
- ✅ Proceso más rápido (solo 2 campos)
- ✅ No maneja contraseñas temporales manualmente
- ✅ Confirmación automática de envío

### **Para el Administrador**
- ✅ Recibe credenciales por email seguro
- ✅ Define su propia contraseña
- ✅ Proceso guiado paso a paso
- ✅ Validación de datos en tiempo real

### **Para el Sistema**
- ✅ Mayor seguridad (tokens temporales)
- ✅ Trazabilidad completa del proceso
- ✅ Prevención de cuentas abandonadas
- ✅ Email profesional automático

## Configuración Requerida

### **Mail Configuration**
Asegurar que Laravel esté configurado para envío de emails:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ticketsair.com
MAIL_FROM_NAME="TicketsAir"
```

### **URL Configuration**
La URL para completar registro se genera automáticamente:
```
https://your-domain.com/complete-admin-registration?token=TOKEN&email=EMAIL
```

## Seguridad

### **Medidas Implementadas**
- ✅ Tokens únicos no reutilizables
- ✅ Expiración temporal (24h)
- ✅ Validación de email y token simultánea
- ✅ Contraseñas hasheadas desde el inicio
- ✅ Validación de unicidad de datos

### **Consideraciones**
- Los tokens expiran automáticamente
- Las contraseñas temporales no son reutilizables
- El registro solo puede completarse una vez
- Validación robusta en ambos endpoints

## Testing Manual Recomendado

1. **Crear invitación** → Verificar email recibido
2. **Verificar token** → Probar con token válido/inválido/expirado
3. **Completar registro** → Verificar validaciones
4. **Login posterior** → Confirmar credenciales definitivas
5. **Estados de error** → Token expirado, datos duplicados