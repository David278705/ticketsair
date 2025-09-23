# Documentación Frontend - Restricciones Usuario Root

## Resumen de Cambios en las Vistas

Se han implementado modificaciones en el frontend para reflejar las nuevas restricciones del usuario **root**, asegurando una interfaz coherente con las limitaciones del backend.

## Archivos Modificados

### 1. `/resources/js/pages/admin/UserManagement.vue`

#### Cambios Principales:
- **Alerta informativa**: Se agregó una alerta amarilla visible solo para el usuario root que explica las restricciones
- **Nuevo botón**: "Cambiar Mi Contraseña" (verde) visible solo para usuarios root
- **Restricción de acciones**: Los botones "Editar" y "Resetear contraseña" ahora están ocultos para usuarios root
- **Mensaje explicativo**: En lugar de los botones de acción, se muestra "Solo lectura (funcionalidad restringida)"

#### Estructura de Botones por Rol:
**Para usuarios ROOT:**
- ✅ Ver listado de usuarios
- ✅ Crear administradores (botón azul "Crear Admin")
- ✅ Cambiar propia contraseña (botón verde "Cambiar Mi Contraseña")
- ✅ Activar/desactivar usuarios no-root
- ❌ Editar usuarios (botón oculto)
- ❌ Resetear contraseñas (botón oculto)

**Para usuarios ADMIN:**
- ✅ Todas las funcionalidades originales sin restricciones

### 2. `/resources/js/components/admin/ChangeOwnPasswordModal.vue` (NUEVO)

Nuevo componente modal especializado para que el usuario root cambie su propia contraseña.

#### Características:
- **Validación de seguridad**: Requiere contraseña actual para confirmación
- **Indicador de fortaleza**: Muestra visualmente qué tan segura es la nueva contraseña
- **Campos con visibilidad toggle**: Botones para mostrar/ocultar contraseñas
- **Alerta informativa**: Explicación sobre las restricciones del usuario root
- **Validación robusta**: Confirma que ambas contraseñas coincidan y cumplan requisitos
- **Feedback visual**: Indicadores de color para la fortaleza (rojo/amarillo/verde)

#### API Integration:
- Conecta con el endpoint `POST /api/admin/change-own-password`
- Manejo de errores específicos del backend
- Validación del lado cliente y servidor

## Flujo de Usuario Root

### 1. **Acceso al Panel de Usuarios**
```
Usuario Root login → Dashboard → Gestión de Usuarios
```
- Ve alerta amarilla explicando limitaciones
- Ve botón verde "Cambiar Mi Contraseña"
- Ve botón azul "Crear Admin"

### 2. **Visualización de Usuarios**
- Lista completa de usuarios (solo lectura)
- Para cada usuario no-root: botón activar/desactivar
- Para usuarios root: no hay botones de acción
- Mensaje "Solo lectura (funcionalidad restringida)" en columna de acciones

### 3. **Cambio de Contraseña Propia**
```
Click "Cambiar Mi Contraseña" → Modal → Llenar formulario → Confirmar
```
- Modal con campos de contraseña actual, nueva y confirmación
- Indicador visual de fortaleza de contraseña
- Validación en tiempo real
- Mensaje de éxito al completar

### 4. **Crear Administrador**
```
Click "Crear Admin" → Modal original → Llenar datos → Crear
```
- Funcionalidad intacta sin cambios
- Modal original preservado

## Elementos de UI

### Colores y Estilos:
- **Alerta de restricción**: Fondo amarillo (`bg-yellow-50`), borde amarillo (`border-yellow-200`)
- **Botón cambio contraseña**: Verde (`bg-green-600` hover `bg-green-700`)
- **Botón crear admin**: Azul (sin cambios)
- **Indicador de fortaleza**: Rojo/Naranja/Amarillo/Verde según fortaleza

### Iconos Utilizados:
- `AlertCircle`: Icono de alerta en notificación informativa
- `Key`: Icono para botón de cambiar contraseña
- `Eye`/`EyeOff`: Toggle de visibilidad de contraseñas
- `Info`: Icono informativo en modal

## Validaciones Frontend

### Formulario Cambio de Contraseña:
```javascript
// Validaciones implementadas
- current_password: requerido
- new_password: requerido, mínimo 8 caracteres
- new_password_confirmation: requerido, debe coincidir
- Fortaleza: longitud, mayúsculas, minúsculas, números, símbolos
```

### Lógica Condicional:
```javascript
// Mostrar elementos según rol
v-if="auth.user?.role?.name === 'root'"  // Solo para root
v-if="auth.user?.role?.name !== 'root'"  // Para no-root
```

## Estados y Reactividad

### Estados Agregados:
- `changeOwnPasswordOpen`: Boolean para controlar modal
- `showCurrentPassword`: Toggle visibilidad contraseña actual
- `showNewPassword`: Toggle visibilidad nueva contraseña
- `showConfirmPassword`: Toggle visibilidad confirmación

### Métodos Agregados:
- `openChangeOwnPassword()`: Abre modal de cambio de contraseña
- `onOwnPasswordChanged()`: Callback después de cambio exitoso

## Manejo de Errores

### Errores del Backend:
- Contraseña actual incorrecta → Error específico en campo
- Validaciones fallidas → Errores por campo individual
- Errores de servidor → Alert con mensaje descriptivo

### Mensajes de Usuario:
- Éxito: "Tu contraseña ha sido actualizada exitosamente"
- Error genérico: "Error al cambiar la contraseña"
- Restricciones: Mensajes informativos en español

## Compatibilidad

### Roles Soportados:
- ✅ ROOT: Interfaz con restricciones implementadas
- ✅ ADMIN: Interfaz original sin cambios
- ✅ CLIENT: No afectado (no accede a panel admin)
- ✅ VISITOR: No afectado (no accede a panel admin)

### Responsive Design:
- Modal responsivo en dispositivos móviles
- Botones apilables en pantallas pequeñas
- Alerta adaptable a diferentes tamaños

## Testing Manual Recomendado

1. **Login como ROOT** → Verificar alerta informativa y botones correctos
2. **Cambio de contraseña ROOT** → Probar formulario completo y validaciones
3. **Creación de admin** → Confirmar que funcionalidad se mantiene
4. **Login como ADMIN** → Verificar que no hay cambios en funcionalidad
5. **Responsive** → Probar en móvil/tablet
6. **Estados de error** → Probar contraseña incorrecta, validaciones fallidas

## Archivos de Configuración

No se requirieron cambios en:
- Router (`router/index.js`)
- Store de autenticación (`stores/auth.js`)
- Configuración de API (`lib/api.js`)
- Middleware de autenticación

Los cambios son puramente de presentación e interacción, manteniendo la arquitectura existente.