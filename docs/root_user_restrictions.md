# Documentación de Cambios - Restricciones del Usuario Root

## Resumen de Cambios
Se han implementado las siguientes restricciones para el usuario **root**:

### ✅ Funcionalidades PERMITIDAS para Root:
1. **Cambiar su propia contraseña** - Nueva ruta específica
2. **Crear administradores** - Funcionalidad mantenida
3. **Ver listado de usuarios** - Funcionalidad mantenida
4. **Ver detalles de usuarios** - Funcionalidad mantenida
5. **Activar/desactivar usuarios** - Funcionalidad mantenida
6. **Ver roles del sistema** - Funcionalidad mantenida

### ❌ Funcionalidades RESTRINGIDAS para Root:
1. **Editar credenciales de otros usuarios** - DESHABILITADA
2. **Restablecer contraseñas de otros usuarios** - DESHABILITADA

## Archivos Modificados

### 1. `/app/Http/Controllers/Admin/UserAdminController.php`
- **Método `updateCredentials()`**: Ahora retorna error 403 indicando que la funcionalidad está restringida
- **Método `resetPassword()`**: Ahora retorna error 403 indicando que la funcionalidad está restringida
- **Nuevo método `updateOwnPassword()`**: Permite al root cambiar únicamente su propia contraseña

### 2. `/routes/api.php`
- **Rutas deshabilitadas**:
  - `PUT /admin/users/{user}/credentials` - Comentada y deshabilitada
  - `POST /admin/users/{user}/reset-password` - Comentada y deshabilitada
- **Nueva ruta agregada**:
  - `POST /admin/change-own-password` - Para que root cambie su propia contraseña

### 3. `/app/Http/Requests/UpdateCredentialsRequest.php`
- **Método `authorize()`**: Ahora retorna `false` para deshabilitar completamente la funcionalidad

### 4. `/app/Http/Requests/ResetPasswordRequest.php`
- **Método `authorize()`**: Ahora retorna `false` para deshabilitar completamente la funcionalidad

## Nuevas Rutas API

### Cambio de Contraseña Propia (Root)
```http
POST /api/admin/change-own-password
Authorization: Bearer {token_root}
Content-Type: application/json

{
  "current_password": "contraseña_actual",
  "new_password": "nueva_contraseña",
  "new_password_confirmation": "nueva_contraseña"
}
```

**Respuestas:**
- `200`: Contraseña actualizada exitosamente
- `400`: Contraseña actual incorrecta
- `403`: Solo el usuario root puede usar esta funcionalidad
- `422`: Errores de validación

## Validaciones del Nuevo Endpoint

### Campos Requeridos:
- `current_password`: Contraseña actual (obligatorio)
- `new_password`: Nueva contraseña (mínimo 8 caracteres, obligatorio)
- `new_password_confirmation`: Confirmación de nueva contraseña (debe coincidir)

### Seguridad:
- Verificación de que el usuario sea realmente root
- Validación de la contraseña actual antes del cambio
- Encriptación de la nueva contraseña con Hash

## Funcionalidades Mantenidas

### Crear Administrador (Root)
```http
POST /api/admin/users/create-admin
```
Esta funcionalidad permanece **intacta** y sigue siendo exclusiva del usuario root.

### Ver Usuarios y Gestión Básica
- `GET /api/admin/users` - Listar usuarios
- `GET /api/admin/users/{user}` - Ver detalles de usuario
- `PATCH /api/admin/users/{user}/toggle-status` - Activar/desactivar usuario
- `GET /api/admin/roles` - Ver roles del sistema

## Impacto en el Frontend

### Interfaces a Actualizar:
1. **Panel de edición de usuarios**: Remover opciones de edición para root
2. **Gestión de contraseñas**: Remover opción de resetear contraseñas para root
3. **Agregar nuevo formulario**: Para cambio de contraseña propia del root

### Mensajes de Error:
Los nuevos endpoints retornan mensajes descriptivos en español explicando las restricciones implementadas.

## Consideraciones de Seguridad

- ✅ El root mantiene acceso de solo lectura a datos de usuarios
- ✅ El root puede seguir creando administradores
- ✅ El root solo puede modificar su propia contraseña de forma segura
- ✅ Se previene la edición accidental o no autorizada de datos de usuarios
- ✅ Todas las validaciones y middleware siguen funcionando correctamente

## Testing Recomendado

1. Verificar que root puede cambiar su propia contraseña
2. Confirmar que root NO puede editar datos de otros usuarios
3. Validar que root puede seguir creando administradores
4. Probar que las rutas deshabilitadas retornan error 403
5. Verificar que el frontend maneje correctamente los nuevos mensajes de error