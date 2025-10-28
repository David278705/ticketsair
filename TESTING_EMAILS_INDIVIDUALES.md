# 🧪 Guía de Testing - Sistema de Emails Individuales

## ✅ Pre-requisitos

1. ✅ Servidor Laravel corriendo: `php artisan serve --port=8000`
2. ✅ Vite corriendo: `npm run dev`
3. ✅ Mailtrap configurado en `.env`
4. ✅ Cache limpiada: `php artisan view:clear && php artisan config:clear`

---

## 📋 Test 1: Email Obligatorio

### **Objetivo**: Verificar que no se puede crear booking sin email

### **Pasos**:

1. Ir a `/` (página principal)
2. Buscar un vuelo disponible
3. Click en "Comprar" o "Reservar"
4. Ingresar datos de pasajero **SIN email**
5. Click en "Continuar"

### **Resultado Esperado**:

```
❌ Error de validación
"El campo email es obligatorio"
```

### **Status**: [ ] Pendiente [ ] ✅ Aprobado [ ] ❌ Fallo

---

## 📋 Test 2: Compra Individual (1 Pasajero)

### **Objetivo**: Verificar email personalizado para 1 pasajero

### **Pasos**:

1. Buscar vuelo disponible
2. Click en "Comprar"
3. Ingresar datos de 1 pasajero:
    ```
    Nombre: Juan
    Apellido: Pérez
    DNI: 1234567890
    Email: juan@test.com
    Fecha nacimiento: 01/01/1990
    Género: M
    ```
4. Click "Continuar"
5. Ingresar datos de tarjeta (simulado)
6. Confirmar compra

### **Resultado Esperado**:

#### **Email en Mailtrap**:

```
Para: juan@test.com
Asunto: ✅ Confirmación de Compra - Vuelo [CÓDIGO]

Hola Juan,

Tu Información Personal
- Pasajero: JUAN PÉREZ
- DNI: 1234567890
- Asiento: [ASIENTO]
- Clase: Económica

[NO debe aparecer sección "Otros Pasajeros"]

Próximos Pasos
1. Check-in: 24 horas antes...
```

#### **Log en Laravel**:

```
[INFO] Email de compra enviado a: juan@test.com (Juan Pérez)
```

### **Status**: [ ] Pendiente [ ] ✅ Aprobado [ ] ❌ Fallo

---

## 📋 Test 3: Compra Grupal (3 Pasajeros)

### **Objetivo**: Verificar emails individuales personalizados

### **Pasos**:

1. Buscar vuelo disponible
2. Click en "Comprar"
3. Agregar 3 pasajeros:

    ```
    Pasajero 1:
    - Nombre: Juan Pérez
    - Email: juan@test.com
    - DNI: 1111111111

    Pasajero 2:
    - Nombre: María García
    - Email: maria@test.com
    - DNI: 2222222222

    Pasajero 3:
    - Nombre: Pedro López
    - Email: pedro@test.com
    - DNI: 3333333333
    ```

4. Confirmar compra

### **Resultado Esperado**:

#### **Email 1 (juan@test.com)**:

```
Hola Juan,

Tu Información Personal
- Pasajero: JUAN PÉREZ
- DNI: 1111111111
- Asiento: 12A

Otros Pasajeros en esta Reserva
• María García (2222222222) - Asiento: 12B
• Pedro López (3333333333) - Asiento: 12C
```

#### **Email 2 (maria@test.com)**:

```
Hola María,

Tu Información Personal
- Pasajero: MARÍA GARCÍA
- DNI: 2222222222
- Asiento: 12B

Otros Pasajeros en esta Reserva
• Juan Pérez (1111111111) - Asiento: 12A
• Pedro López (3333333333) - Asiento: 12C
```

#### **Email 3 (pedro@test.com)**:

```
Hola Pedro,

Tu Información Personal
- Pasajero: PEDRO LÓPEZ
- DNI: 3333333333
- Asiento: 12C

Otros Pasajeros en esta Reserva
• Juan Pérez (1111111111) - Asiento: 12A
• María García (2222222222) - Asiento: 12B
```

#### **Log en Laravel**:

```
[INFO] Email de compra enviado a: juan@test.com (Juan Pérez)
[INFO] Email de compra enviado a: maria@test.com (María García)
[INFO] Email de compra enviado a: pedro@test.com (Pedro López)
```

### **Status**: [ ] Pendiente [ ] ✅ Aprobado [ ] ❌ Fallo

---

## 📋 Test 4: Reserva Individual

### **Objetivo**: Verificar email de reserva personalizado

### **Pasos**:

1. Buscar vuelo disponible
2. Click en "Reservar"
3. Ingresar datos de 1 pasajero con email
4. Confirmar reserva

### **Resultado Esperado**:

#### **Email en Mailtrap**:

```
Para: pasajero@test.com
Asunto: Confirmación de Reserva - TicketsAir

Hola [Nombre],

Tu Información Personal
- Pasajero: [NOMBRE COMPLETO]
- DNI: [DNI]
- Asiento: [ASIENTO]

Código de Reserva: RES-XXXXXX

⚠️ IMPORTANTE: Esta reserva expira el [FECHA] (24 horas)
```

### **Status**: [ ] Pendiente [ ] ✅ Aprobado [ ] ❌ Fallo

---

## 📋 Test 5: Check-in Individual (1 Pasajero)

### **Objetivo**: Verificar PDF sin sección "Otros Pasajeros"

### **Pasos**:

1. Completar Test 2 (compra de 1 pasajero)
2. Esperar o modificar vuelo para que esté dentro de 24h
3. Ir a "Mis Viajes"
4. Click en "Check-in" del vuelo
5. Ingresar código de ticket o DNI

### **Resultado Esperado**:

#### **Email en Mailtrap**:

```
Para: juan@test.com
Asunto: ✈️ Tu Pasabordo - Vuelo [CÓDIGO]

Adjunto: Pasabordo_TKT-XXXXXX.pdf
```

#### **PDF Generado** (`storage/app/public/boarding-passes/`):

```
┌─────────────────────────────────────┐
│ PASAJERO PRINCIPAL                  │
│ JUAN PÉREZ                          │
│ DNI: 1234567890                     │
│ Asiento: 12A                        │
└─────────────────────────────────────┘

[NO debe aparecer sección "Otros Pasajeros"]

┌─────────────────────────────────────┐
│ ||||||||||||||||||||||||||||||     │
│ TKT-XXXXXX                          │
└─────────────────────────────────────┘
```

#### **Log en Laravel**:

```
[INFO] Pasabordo enviado a: juan@test.com para ticket TKT-XXXXXX
```

### **Status**: [ ] Pendiente [ ] ✅ Aprobado [ ] ❌ Fallo

---

## 📋 Test 6: Check-in Grupal (3 Pasajeros)

### **Objetivo**: Verificar PDFs individuales con lista de compañeros

### **Pasos**:

1. Completar Test 3 (compra de 3 pasajeros)
2. Hacer check-in del **Pasajero 1** (Juan)
3. Hacer check-in del **Pasajero 2** (María)
4. Hacer check-in del **Pasajero 3** (Pedro)

### **Resultado Esperado**:

#### **Check-in 1 (Juan)**:

**Email**: Solo a `juan@test.com`  
**PDF**:

```
PASAJERO PRINCIPAL
JUAN PÉREZ (1111111111) - Asiento: 12A

📋 OTROS PASAJEROS EN ESTA RESERVA
• MARÍA GARCÍA (2222222222) - Asiento: 12B
• PEDRO LÓPEZ (3333333333) - Asiento: 12C
```

#### **Check-in 2 (María)**:

**Email**: Solo a `maria@test.com`  
**PDF**:

```
PASAJERO PRINCIPAL
MARÍA GARCÍA (2222222222) - Asiento: 12B

📋 OTROS PASAJEROS EN ESTA RESERVA
• JUAN PÉREZ (1111111111) - Asiento: 12A
• PEDRO LÓPEZ (3333333333) - Asiento: 12C
```

#### **Check-in 3 (Pedro)**:

**Email**: Solo a `pedro@test.com`  
**PDF**:

```
PASAJERO PRINCIPAL
PEDRO LÓPEZ (3333333333) - Asiento: 12C

📋 OTROS PASAJEROS EN ESTA RESERVA
• JUAN PÉREZ (1111111111) - Asiento: 12A
• MARÍA GARCÍA (2222222222) - Asiento: 12B
```

#### **Logs en Laravel**:

```
[INFO] Pasabordo enviado a: juan@test.com para ticket TKT-ABC123
[INFO] Pasabordo enviado a: maria@test.com para ticket TKT-ABC124
[INFO] Pasabordo enviado a: pedro@test.com para ticket TKT-ABC125
```

### **Status**: [ ] Pendiente [ ] ✅ Aprobado [ ] ❌ Fallo

---

## 📋 Test 7: Dueño de Cuenta NO Recibe Duplicados

### **Objetivo**: Verificar que el dueño NO recibe copias

### **Pasos**:

1. Login como usuario: `user@test.com`
2. Comprar vuelo con 2 pasajeros:
    - Pasajero 1: `pasajero1@test.com`
    - Pasajero 2: `pasajero2@test.com`
3. Revisar Mailtrap

### **Resultado Esperado**:

#### **Emails Recibidos**:

-   ✅ `pasajero1@test.com` - Email de compra
-   ✅ `pasajero2@test.com` - Email de compra
-   ❌ `user@test.com` - NO debe recibir nada

### **Status**: [ ] Pendiente [ ] ✅ Aprobado [ ] ❌ Fallo

---

## 📋 Test 8: Conversión Reserva → Compra

### **Objetivo**: Verificar emails en conversión

### **Pasos**:

1. Crear reserva con 2 pasajeros
2. Ir a "Mis Viajes"
3. Click en "Completar Compra"
4. Ingresar datos de pago
5. Confirmar

### **Resultado Esperado**:

#### **Emails Individuales**:

-   ✅ Cada pasajero recibe email de compra personalizado
-   ✅ Logs muestran envíos individuales

### **Status**: [ ] Pendiente [ ] ✅ Aprobado [ ] ❌ Fallo

---

## 🐛 Troubleshooting

### **Problema 1: No llegan emails**

```bash
# Verificar configuración Mailtrap
php artisan tinker
Mail::raw('Test', function($m) { $m->to('test@test.com')->subject('Test'); });

# Ver logs
tail -f storage/logs/laravel.log
```

### **Problema 2: PDF no se genera**

```bash
# Verificar permisos
chmod -R 775 storage/app/public/boarding-passes

# Verificar enlace simbólico
php artisan storage:link

# Listar PDFs generados
ls -la storage/app/public/boarding-passes/
```

### **Problema 3: Error en vista Blade**

```bash
# Limpiar caché
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

---

## ✅ Checklist Final

### **Funcionalidad**

-   [ ] Email obligatorio validado correctamente
-   [ ] Emails individuales personalizados (compra)
-   [ ] Emails individuales personalizados (reserva)
-   [ ] PDF muestra pasajero principal
-   [ ] PDF muestra otros pasajeros (si aplica)
-   [ ] PDFs individuales por pasajero en check-in
-   [ ] Dueño NO recibe copias

### **UI/UX**

-   [ ] Mensajes de error claros
-   [ ] Confirmación de compra/reserva visible
-   [ ] Redirección a "Mis Viajes" funciona
-   [ ] Check-in accesible desde "Mis Viajes"

### **Logs**

-   [ ] Logs muestran emails enviados con nombre
-   [ ] Logs muestran errores de envío
-   [ ] Logs muestran generación de PDF

### **Mailtrap**

-   [ ] Emails llegan correctamente
-   [ ] Formato profesional
-   [ ] PDFs adjuntos correctos
-   [ ] Sin duplicados

---

## 📊 Reporte de Testing

**Fecha**: ******\_\_\_******  
**Tester**: ******\_\_\_******

| Test                   | Status        | Notas |
| ---------------------- | ------------- | ----- |
| 1. Email Obligatorio   | [ ] ✅ [ ] ❌ |       |
| 2. Compra Individual   | [ ] ✅ [ ] ❌ |       |
| 3. Compra Grupal       | [ ] ✅ [ ] ❌ |       |
| 4. Reserva Individual  | [ ] ✅ [ ] ❌ |       |
| 5. Check-in Individual | [ ] ✅ [ ] ❌ |       |
| 6. Check-in Grupal     | [ ] ✅ [ ] ❌ |       |
| 7. Sin Duplicados      | [ ] ✅ [ ] ❌ |       |
| 8. Conversión Reserva  | [ ] ✅ [ ] ❌ |       |

**Resultado Global**: [ ] ✅ Aprobado [ ] ❌ Requiere correcciones

---

**Versión**: 2.0.0  
**Fecha**: 24 de octubre de 2025
