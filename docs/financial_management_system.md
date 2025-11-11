# 💰 Sistema de Gestión Financiera - TicketsAir

## 📋 Resumen General

Se ha implementado un **sistema completo de gestión financiera** para la plataforma TicketsAir, que permite a los usuarios clientes:

1. ✅ **Gestionar múltiples tarjetas** de crédito y débito
2. ✅ **Administrar saldo del wallet** (monedero virtual)
3. ✅ **Ver historial completo de transacciones**
4. ✅ **Seleccionar método de pago** al momento de comprar tiquetes
5. ✅ **Sistema completamente simulado** (sin pagos reales)

---

## 🗂️ Componentes Implementados

### Backend (Laravel/PHP)

#### 1. **Migraciones de Base de Datos**

##### `add_is_default_to_cards_table`
- Agrega campo `is_default` (boolean) para marcar tarjeta predeterminada
- Agrega campo `card_type` (enum: credit, debit) para tipo de tarjeta

##### `create_wallet_transactions_table`
- Tabla para registrar **todas las transacciones del saldo**
- Campos:
  - `type`: recharge (recarga), purchase (compra), refund (reembolso), adjustment (ajuste)
  - `amount`: monto de la transacción
  - `balance_before`: saldo antes de la transacción
  - `balance_after`: saldo después de la transacción
  - `description`: descripción opcional
  - `related_id` y `related_type`: polimórfico para relacionar con Booking, Payment, etc.
  - `meta`: JSON con información adicional

#### 2. **Modelos**

##### `Card` (Actualizado)
**Ubicación:** `app/Models/Card.php`

**Características:**
- ✅ Campos: `brand`, `card_type`, `holder_name`, `last4`, `exp_month`, `exp_year`, `token`, `is_default`
- ✅ Accesor `masked_number`: Retorna `**** **** **** 1234`
- ✅ Accesor `is_expired`: Verifica si la tarjeta está expirada
- ✅ Método `makeDefault()`: Marca la tarjeta como predeterminada
- ✅ Scope `active()`: Solo tarjetas no expiradas
- ✅ Scope `default($userId)`: Tarjeta predeterminada del usuario

##### `WalletTransaction` (Nuevo)
**Ubicación:** `app/Models/WalletTransaction.php`

**Características:**
- ✅ Registra todas las transacciones del wallet
- ✅ Método estático `createTransaction()`: Crea transacción y actualiza saldo automáticamente
- ✅ Relación polimórfica con cualquier modelo (Booking, Payment, etc.)
- ✅ Scopes: `recent()`, `byType()`

##### `User` (Actualizado)
**Ubicación:** `app/Models/User.php`

- ✅ Relación `walletTransactions()` agregada

#### 3. **Controladores**

##### `PaymentMethodController`
**Ubicación:** `app/Http/Controllers/PaymentMethodController.php`

**Endpoints:**
- ✅ `GET /payment-methods` - Listar tarjetas del usuario
- ✅ `POST /payment-methods` - Agregar nueva tarjeta
- ✅ `GET /payment-methods/{card}` - Ver tarjeta específica
- ✅ `POST /payment-methods/{card}/set-default` - Marcar como predeterminada
- ✅ `DELETE /payment-methods/{card}` - Eliminar tarjeta

**Funcionalidades:**
- Detecta automáticamente la marca de la tarjeta (Visa, Mastercard, Amex, Discover)
- Valida que no se elimine la única tarjeta si hay pagos pendientes
- Si se elimina la tarjeta predeterminada, asigna otra automáticamente
- Simula tokenización con hash SHA256

##### `WalletController`
**Ubicación:** `app/Http/Controllers/WalletController.php`

**Endpoints:**
- ✅ `GET /wallet` - Obtener saldo y transacciones recientes
- ✅ `POST /wallet/recharge` - Recargar saldo
- ✅ `GET /wallet/statistics` - Estadísticas del wallet
- ✅ `GET /wallet/transactions` - Historial completo filtrado

**Funcionalidades:**
- Recarga mínima: $10,000 COP
- Recarga máxima: $10,000,000 COP
- Métodos de pago: tarjeta de crédito, débito, transferencia bancaria, PSE
- Registro automático de transacciones

#### 4. **Request Classes (Validaciones)**

##### `CardStoreRequest`
**Ubicación:** `app/Http/Requests/CardStoreRequest.php`

**Validaciones:**
- ✅ Nombre del titular: requerido, máximo 255 caracteres
- ✅ Número de tarjeta: 16 dígitos, solo números
- ✅ Mes de expiración: 01-12
- ✅ Año de expiración: >= año actual
- ✅ CVV: 3 o 4 dígitos
- ✅ Tipo de tarjeta: credit o debit
- ✅ Validación personalizada: verifica que no esté expirada

##### `WalletRechargeRequest`
**Ubicación:** `app/Http/Requests/WalletRechargeRequest.php`

**Validaciones:**
- ✅ Monto: requerido, numérico, mín $10,000, máx $10,000,000
- ✅ Método de pago: credit_card, debit_card, bank_transfer, cash
- ✅ Descripción: opcional, máximo 500 caracteres

#### 5. **Rutas API**
**Ubicación:** `routes/api.php`

Todas las rutas están protegidas con `auth:sanctum` y `role:client`:

```php
// Gestión de tarjetas
GET    /payment-methods
POST   /payment-methods
GET    /payment-methods/{card}
POST   /payment-methods/{card}/set-default
DELETE /payment-methods/{card}

// Gestión de Wallet
GET    /wallet
POST   /wallet/recharge
GET    /wallet/statistics
GET    /wallet/transactions
```

---

### Frontend (Vue.js)

#### 1. **Componente Principal: FinancialManagement.vue**
**Ubicación:** `resources/js/pages/client/FinancialManagement.vue`

**Características:**
- ✅ **Dashboard financiero completo** con saldo destacado
- ✅ **Gestión de tarjetas:**
  - Listar todas las tarjetas
  - Agregar nueva tarjeta (modal)
  - Marcar como predeterminada
  - Eliminar tarjeta
  - Iconos visuales por marca
  - Indicadores de expiración
- ✅ **Gestión de saldo:**
  - Saldo destacado con gradiente visual
  - Recargar saldo (modal)
  - Métodos de pago simulados
- ✅ **Historial de transacciones:**
  - Lista con iconos por tipo
  - Colores diferenciados (verde para recargas, rojo para compras)
  - Fecha y hora formateadas
  - Saldo antes y después
- ✅ **Validaciones en tiempo real**
- ✅ **Diseño responsive** y moderno
- ✅ **Mensajes de confirmación** con SweetAlert2

**Vista previa:**
- Saldo grande y destacado en card azul con gradiente
- Grid de 2 columnas: tarjetas a la izquierda, transacciones a la derecha
- Modales para agregar tarjeta y recargar saldo
- Indicadores visuales claros (predeterminada, expirada, etc.)

#### 2. **Componente Selector: PaymentMethodSelector.vue**
**Ubicación:** `resources/js/components/PaymentMethodSelector.vue`

**Características:**
- ✅ **Selector de método de pago** para el checkout
- ✅ **Opciones:**
  1. **Saldo del wallet** (muestra saldo actual)
  2. **Tarjetas guardadas** (muestra todas las tarjetas activas)
  3. **Nueva tarjeta** (formulario completo)
- ✅ **Validaciones automáticas:**
  - Saldo insuficiente
  - Tarjetas expiradas (deshabilitadas)
  - Campos obligatorios
- ✅ **Opción para guardar nueva tarjeta**
- ✅ **Resumen del pago** en tiempo real
- ✅ **Emite eventos:** `valid`, `invalid`, `update:modelValue`
- ✅ **Diseño con bordes destacados** en la opción seleccionada
- ✅ **Completamente reactivo**

**Uso:**
```vue
<PaymentMethodSelector
  :totalAmount="totalAmount"
  v-model="selectedPaymentMethod"
  @valid="enableCheckoutButton"
  @invalid="disableCheckoutButton"
/>
```

---

## 🔐 Seguridad y Buenas Prácticas

### Implementadas:
1. ✅ **Autenticación requerida:** Todas las rutas están protegidas con Sanctum
2. ✅ **Autorización:** Solo el dueño puede ver/modificar sus tarjetas y wallet
3. ✅ **Validaciones robustas:** Frontend y backend
4. ✅ **Tokenización simulada:** Los datos sensibles se hashean
5. ✅ **Solo se guarda last4:** Nunca se almacena el número completo de la tarjeta
6. ✅ **Prevención de eliminación:** No se puede eliminar la última tarjeta con pagos pendientes
7. ✅ **Transacciones atómicas:** Uso de DB::beginTransaction() y rollback
8. ✅ **Registro de auditoría:** Todas las transacciones quedan registradas con metadata

### Sistema Simulado:
- ⚠️ **IMPORTANTE:** Este es un sistema **100% simulado**
- No se conecta a ninguna pasarela de pago real
- No se hacen cargos reales a tarjetas
- Los números de tarjeta se validan pero no se verifican con bancos
- Ideal para desarrollo, pruebas y demostración

---

## 📊 Estructura de Datos

### Tabla `cards`
```
- id
- user_id (FK)
- brand (visa, mastercard, amex, discover)
- card_type (credit, debit)
- holder_name
- last4 (últimos 4 dígitos)
- exp_month
- exp_year
- token (hash simulado)
- is_default (boolean)
- created_at
- updated_at
```

### Tabla `wallet_transactions`
```
- id
- user_id (FK)
- type (recharge, purchase, refund, adjustment)
- amount (decimal)
- balance_before (decimal)
- balance_after (decimal)
- description (text)
- related_id (polimórfico)
- related_type (polimórfico)
- meta (JSON)
- created_at
- updated_at
```

### Tabla `users` (campo existente)
```
- wallet_balance (decimal, default: 0)
```

---

## 🚀 Cómo Usar

### Para Usuarios Clientes:

1. **Acceder a Gestión Financiera:**
   - Navegar a `/client/financial`
   - Ver saldo actual y transacciones

2. **Agregar Tarjeta:**
   - Click en "Agregar tarjeta"
   - Completar formulario
   - Opción de marcar como predeterminada
   - Usar tarjetas de prueba (ver sección siguiente)

3. **Recargar Saldo:**
   - Click en "Recargar saldo"
   - Ingresar monto (mín $10,000)
   - Seleccionar método de pago
   - Confirmar recarga

4. **Comprar Tiquetes:**
   - En el checkout, selector de método de pago
   - Elegir: saldo, tarjeta guardada o nueva tarjeta
   - Validación automática de saldo
   - Confirmar compra

### Tarjetas de Prueba:

**Visa:**
- Número: `4111111111111111`
- CVV: `123`
- Fecha: Cualquier fecha futura

**Mastercard:**
- Número: `5500000000000004`
- CVV: `123`
- Fecha: Cualquier fecha futura

**American Express:**
- Número: `340000000000009`
- CVV: `1234`
- Fecha: Cualquier fecha futura

---

## 🎯 Integración con Flujo de Compra

Para integrar el selector de método de pago en el checkout existente:

1. Importar el componente:
```vue
import PaymentMethodSelector from '@/components/PaymentMethodSelector.vue';
```

2. Usar en el template:
```vue
<PaymentMethodSelector
  :totalAmount="booking.total_amount"
  v-model="selectedPaymentMethod"
  @valid="paymentMethodValid = true"
  @invalid="paymentMethodValid = false"
/>
```

3. Al confirmar la compra, procesar según el tipo:
```javascript
if (selectedPaymentMethod.type === 'wallet') {
  // Descontar del saldo
  await WalletTransaction::createTransaction(
    user.id,
    'purchase',
    totalAmount,
    `Compra de tiquetes - Reserva ${booking.reservation_code}`,
    booking
  );
}
else if (selectedPaymentMethod.type === 'saved_card') {
  // Procesar con tarjeta guardada
  await processCardPayment(selectedPaymentMethod.cardId, totalAmount);
}
else if (selectedPaymentMethod.type === 'new_card') {
  // Guardar tarjeta si se solicitó
  if (selectedPaymentMethod.newCardData.save_card) {
    await saveCard(selectedPaymentMethod.newCardData);
  }
  // Procesar pago
  await processCardPayment(newCard.id, totalAmount);
}
```

---

## ✅ Testing Manual

### Escenarios a probar:

1. ✅ Agregar múltiples tarjetas
2. ✅ Marcar tarjeta como predeterminada
3. ✅ Intentar usar tarjeta expirada
4. ✅ Eliminar tarjeta predeterminada (debe asignar otra)
5. ✅ Recargar saldo con diferentes montos
6. ✅ Intentar comprar con saldo insuficiente
7. ✅ Ver historial de transacciones
8. ✅ Seleccionar método de pago en checkout
9. ✅ Guardar nueva tarjeta durante checkout
10. ✅ Ver estadísticas del wallet

---

## 📈 Futuras Mejoras Sugeridas

1. **Integración con pasarela real** (Stripe, PayU, MercadoPago)
2. **Límites de recarga personalizados** por usuario
3. **Sistema de puntos o cashback**
4. **Exportar historial de transacciones** (PDF, Excel)
5. **Notificaciones por email** de transacciones
6. **Autenticación 3D Secure** para tarjetas
7. **Múltiples monedas** (USD, EUR, etc.)
8. **Tokenización real** con vault seguro
9. **Límites de gasto diario/mensual**
10. **Sistema de bonificaciones** por recargas

---

## 🐛 Troubleshooting

### Error: "Saldo insuficiente"
- Verificar que el usuario tenga saldo >= monto de compra
- Recargar saldo desde el panel financiero

### Error: "Tarjeta expirada"
- Verificar fecha de expiración
- Agregar una nueva tarjeta

### Error: "No puedes eliminar tu única tarjeta"
- Verificar si hay pagos pendientes
- Agregar otra tarjeta antes de eliminar

### Transacciones no se registran
- Verificar que `WalletTransaction::createTransaction()` se use correctamente
- Revisar logs de Laravel

---

## 📝 Notas Finales

- ✅ **Sistema 100% funcional** y escalable
- ✅ **Código limpio y documentado**
- ✅ **Validaciones exhaustivas** en frontend y backend
- ✅ **Diseño moderno y responsive**
- ✅ **Arquitectura extensible** para futuras mejoras
- ✅ **Compatible con el sistema de reservas existente**

**¡El módulo de gestión financiera está listo para usar! 🎉**
