# ECOLINDUS Inventario

Sistema web para la gestión de inventario de productos e insumos en una embasadora de agua. Desarrollado con Laravel 12 y PostgreSQL. Incluye trazabilidad de movimientos, control de stock, reportes y dashboard.

## Módulos del Sistema

### 1. **Módulo de Inventario** (Principal)
- Gestión de productos: crear, editar, eliminar
- Control de stock: entradas, salidas, productos dañados
- Stock mínimo configurable por producto
- Historial completo de movimientos
- Reportes: ventas por cliente, ventas por producto
- Dashboard con indicadores en tiempo real

### 2. **Módulo de Facturación Mejorado**
- **RUC Configurable**: Valor centralizado en `config/company.php`
  - Configurar a través de variable de entorno: `COMPANY_RUC`
  - Muestra automática en PDF de facturas
  - Ejemplo: `COMPANY_RUC=20000000000` en `.env`

- **Numeración Manual de Facturas**
  - Posibilidad de editar el número de factura después de emitida
  - Acceso desde vista de detalle: botón "Editar Numeración"
  - Ruta: `PATCH /invoices/{invoice}/update-number`
  - Validación: número único no duplicado
  - Flag `manually_set_number` para auditoría

- **Detalles de Pago en Factura**
  - Registro de pagos con split: efectivo vs transferencia
  - Tabla `invoice_payments`: campos `cash_amount` y `transfer_amount`
  - Resumen automático en PDF con desglose de pago

### 3. **Módulo de Caja (Cash Management)**
- **Apertura y Cierre de Sesiones de Caja**
  - Tabla `cash_sessions`: registro de sesiones activas/cerradas
  - Campos: `user_id`, `opened_at`, `closed_at`, `opening_amount`, `closing_amount`, `status`
  - Estados: `open`, `closed`
  - Auditoría completa de quién abre/cierra y cuándo

- **Control de Caja en Dashboard**
  - Interfaz integrada para abrir nueva sesión
  - Formulario para cerrar sesión con monto de cierre
  - Resumen en tiempo real vía AJAX (actualización cada 30 segundos)
  - Vista de sesiones activas y cerradas

- **Rutas**
  - `POST /cash-sessions/open` - Abrir nueva sesión
  - `POST /cash-sessions/close` - Cerrar sesión (el sistema calcula `closing_amount` usando pagos registrados)
  - `GET /cash-sessions/summary` - Obtener resumen JSON (incluye totales, desglose y listado de facturas)

Features:
- Al cerrar la caja, la UI muestra un resumen con `opening_amount`, `total facturado`, `total efectivo`, `total transferencia` y `monto esperado` antes de confirmar.
- Botón "Ver Resumen" abre un modal con detalle: usuario, apertura, totales, listado de facturas y desglose de pagos. El modal permite imprimir/exportar.
- El cierre acepta un `reported_closing_amount` desde la UI para auditoría pero la base usa la suma calculada de `invoice_payments`.

### 4. **Formas de Pago Detalladas**
- Modelo `InvoicePayment` con relación 1-a-1 con Invoice
- Campos:
  - `cash_amount`: monto pagado en efectivo
  - `transfer_amount`: monto pagado por transferencia
  - Validación: suma debe igualar total de factura
- Integración en PDF: muestra desglose de pago
- Modal de registro con validación en tiempo real

### Sistemas Removidos (Deprecados)
- ❌ Gestión de Proveedores: rutas y controlador desactivados
- ❌ Movimientos de Inventario Manual: funcionalidad integrada en invoice items
- Datos históricos preservados en base de datos (sin eliminación)

## Configuración de Empresa

Archivo: `config/company.php`

```php
return [
    'ruc' => env('COMPANY_RUC', '20000000000'),
    'name' => env('COMPANY_NAME', 'ECOLINDUS'),
    'address' => env('COMPANY_ADDRESS', ''),
    'phone' => env('COMPANY_PHONE', ''),
    'email' => env('COMPANY_EMAIL', ''),
];
```

Variables de entorno (.env):
```
COMPANY_RUC=20000000000
COMPANY_NAME=ECOLINDUS
COMPANY_ADDRESS=Dirección de la empresa
COMPANY_PHONE=+51987654321
COMPANY_EMAIL=contacto@ecolindus.com
```

## Flujos Principales

### Flujo de Factura con Pago
1. Crear factura (con items y totales)
2. Editar número de factura si es necesario (botón "Editar Numeración")
3. Registrar pago: Efectivo + Transferencia (modal de pago)
4. Imprimir PDF: incluye RUC, items, totales y desglose de pago

### Flujo de Caja
1. Abrir sesión: usuario ingresa monto inicial
2. Sistema registra apertura con timestamp
3. Durante la sesión: se asocian pagos de facturas a la caja
4. Cerrar sesión: usuario ingresa monto final
5. Sistema calcula diferencia y registra cierre

## Tecnologías
- Laravel 12
- PostgreSQL
- Blade + Bootstrap 5
- Laravel Charts
- Git + GitHub