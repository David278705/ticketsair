# Guía de Acceso al Módulo Financiero

## Ubicación del Módulo

El módulo de **Gestión Financiera** está integrado dentro del perfil de usuario como una pestaña dedicada.

### Cómo Acceder

1. **Iniciar sesión** como usuario con rol de "Cliente"
2. **Navegar al perfil** usando cualquiera de estos métodos:
   - Hacer clic en tu nombre de usuario en la barra superior
   - Seleccionar "Mi Perfil" del menú desplegable
   - Navegar directamente a `/perfil`
3. **Seleccionar la pestaña "Gestión Financiera"** en el menú lateral del perfil

### Estructura del Perfil

El perfil está organizado en pestañas en el menú lateral:

- **Información Personal**: Datos personales y de contacto
- **Seguridad**: Cambio de contraseña
- **Gestión Financiera**: Módulo completo de finanzas (solo clientes)

## Funcionalidades Disponibles

### 1. Billetera Virtual
- Visualización del saldo actual
- Estadísticas de ingresos y gastos
- Total de transacciones realizadas
- Recarga de saldo mediante tarjetas guardadas

### 2. Métodos de Pago
- Agregar nuevas tarjetas de crédito/débito
- Visualizar tarjetas guardadas (últimos 4 dígitos)
- Establecer tarjeta predeterminada
- Eliminar tarjetas
- Detección automática del tipo de tarjeta (Visa, Mastercard, etc.)

### 3. Historial de Transacciones
- Listado completo de movimientos
- Filtros por tipo: Recargas, Pagos, Reembolsos, Bonificaciones
- Paginación para cargar más transacciones
- Detalles de cada operación con fecha y monto

## Restricciones de Acceso

### Por Rol de Usuario

| Rol | Acceso a Gestión Financiera |
|-----|---------------------------|
| Cliente | ✅ Acceso completo |
| Admin | ❌ No disponible |
| Root | ❌ No disponible |

**Nota**: Los usuarios Root y Admin no tienen acceso al módulo financiero ya que no realizan compras en el sistema.

## Diseño y Experiencia

- **Diseño profesional**: Sin emojis, colores corporativos
- **Integración modular**: Componente independiente `FinancialManagementTab.vue`
- **Consistente con el perfil**: Sigue los mismos patrones de diseño
- **Responsivo**: Adaptable a dispositivos móviles y escritorio

## Arquitectura Técnica

### Frontend
- **Componente**: `resources/js/components/profile/FinancialManagementTab.vue`
- **Importado en**: `resources/js/pages/UserProfile.vue`
- **Renderizado**: Condicionalmente para usuarios tipo "client"

### Backend
- **Rutas API**: Bajo middleware `auth:sanctum` y `role:client`
  - `GET/POST /payment-methods`
  - `POST /payment-methods/{card}/set-default`
  - `DELETE /payment-methods/{card}`
  - `GET /wallet`
  - `POST /wallet/recharge`
  - `GET /wallet/statistics`
  - `GET /wallet/transactions`

## Solución de Problemas

### No veo la pestaña "Gestión Financiera"

**Causa**: Tu usuario no tiene el rol de "Cliente"

**Solución**: 
1. Verifica tu rol en la base de datos
2. Solo los usuarios con `role.name = 'client'` pueden acceder

### Las tarjetas no se guardan

**Causa**: Error de validación o problema con la API

**Solución**:
1. Verifica que todos los campos estén completos
2. Revisa que el formato de la tarjeta sea válido
3. Comprueba la consola del navegador para errores

### No puedo recargar saldo

**Causa**: No hay tarjetas guardadas

**Solución**:
1. Primero agrega una tarjeta en la sección "Métodos de Pago"
2. Luego podrás usar esa tarjeta para recargar

## Integraciones Futuras

### Checkout de Reservas
El componente `PaymentMethodSelector.vue` está preparado para integrarse en el flujo de compra:
- Permitirá seleccionar entre pago con tarjeta o billetera
- Se validará el saldo antes de procesar pagos con billetera
- Se registrará la transacción automáticamente

**Ubicación futura**: En el proceso de checkout al reservar vuelos

---

**Última actualización**: Noviembre 2025
**Versión del módulo**: 1.0.0- Sistema de Gestión Financiera

## 📍 Ubicación del Módulo

El módulo de **Gestión Financiera** está disponible en **3 ubicaciones** para usuarios clientes:

### 1. **Menú Principal de Navegación**
- **Desktop:** En la barra superior → "💳 Mis Finanzas"
- **Móvil:** Menú hamburguesa → "💳 Mis Finanzas"
- **Ubicación:** Entre "Mis viajes" y "Foro"

### 2. **Menú de Usuario (Dropdown)**
- Click en tu nombre/avatar en la esquina superior derecha
- Aparece el dropdown con tu información
- Click en "💳 Mis Finanzas"
- **Ubicación:** Entre "Mi Perfil" y "Cerrar Sesión"

### 3. **URL Directa**
- Puedes acceder directamente navegando a: `/gestion-financiera`
- URL completa: `http://localhost:8005/gestion-financiera`

---

## 🔐 Requisitos de Acceso

- ✅ **Rol requerido:** Cliente (client)
- ✅ **Autenticación:** Debes estar logueado
- ❌ **No disponible para:** Administradores, Root, Visitantes

---

## 📱 Funcionalidades Disponibles

Una vez dentro del módulo, verás:

### Dashboard Principal
1. **💰 Saldo del Wallet**
   - Saldo disponible destacado
   - Botón para recargar saldo

2. **💳 Mis Tarjetas** (Columna izquierda)
   - Lista de tarjetas guardadas
   - Botón "Agregar tarjeta"
   - Acciones: Marcar como predeterminada, Eliminar

3. **📊 Transacciones Recientes** (Columna derecha)
   - Historial de movimientos
   - Recargas, compras, reembolsos
   - Saldo antes/después de cada transacción

---

## 🛒 Uso en el Proceso de Compra

El selector de método de pago también estará disponible al:
1. Buscar vuelos
2. Seleccionar asientos
3. **Proceder al pago** ← Aquí aparecerá el selector

Opciones de pago:
- Saldo del wallet
- Tarjeta guardada
- Nueva tarjeta (con opción de guardar)

---

## 🎯 Capturas de Pantalla

### Menú Principal (Desktop)
```
[Logo] TicketsAir    Vuelos | Mis viajes | 💳 Mis Finanzas | Foro    [COP ▼] [Usuario ▼]
                                    ↑
                              AQUÍ ESTÁ
```

### Menú de Usuario
```
┌─────────────────────────────┐
│ Juan Pérez                  │
│ juan@example.com            │
│ [Cliente]                   │
├─────────────────────────────┤
│ 👤 Mi Perfil                │
│ 💳 Mis Finanzas       ← AQUÍ│
│ 🚪 Cerrar Sesión            │
└─────────────────────────────┘
```

### Menú Móvil
```
☰ Menú
├─ Vuelos
├─ Mis viajes
├─ 💳 Mis Finanzas    ← AQUÍ
├─ Foro
└─ Mi Perfil
```

---

## ✅ Verificación

Para confirmar que el módulo está correctamente integrado:

1. ✅ Inicia sesión como **cliente**
2. ✅ Busca "💳 Mis Finanzas" en el menú superior
3. ✅ Click en el enlace
4. ✅ Deberías ver el dashboard financiero con:
   - Saldo en grande
   - Sección de tarjetas
   - Sección de transacciones

---

## 🐛 Solución de Problemas

### No veo el enlace "Mis Finanzas"
- **Causa:** No estás logueado como cliente
- **Solución:** Inicia sesión con una cuenta de cliente

### El enlace está pero da error 404
- **Causa:** La ruta no está registrada
- **Solución:** Verificar que `router/index.js` tenga la ruta `/gestion-financiera`

### La página carga pero está en blanco
- **Causa:** El componente no se importó correctamente
- **Solución:** Verificar el import en `router/index.js`:
  ```javascript
  import FinancialManagement from '../pages/client/FinancialManagement.vue';
  ```

### Error de API al cargar datos
- **Causa:** Backend no está ejecutándose
- **Solución:** Iniciar el servidor: `php artisan serve --port=8005`

---

## 📝 Notas Importantes

- El módulo **solo es visible para clientes** logueados
- Administradores y Root **NO tienen acceso** (por diseño)
- El icono 💳 ayuda a identificar visualmente el módulo
- Los datos se cargan automáticamente al entrar

---

## 🎨 Personalización

Si deseas cambiar el nombre o ícono del enlace:

### En `NavBar.vue`:
```vue
<!-- Cambiar texto -->
<RouterLink to="/gestion-financiera">
    🏦 Mi Billetera  <!-- Nueva versión -->
</RouterLink>

<!-- O sin emoji -->
<RouterLink to="/gestion-financiera">
    Mis Finanzas
</RouterLink>
```

---

## 🚀 ¡Listo para Usar!

El módulo está **completamente integrado** y accesible desde múltiples puntos de la aplicación para facilitar el acceso de los usuarios.

**¿Necesitas más ayuda?** Consulta la documentación completa en:
- `docs/financial_management_system.md`
