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
4. Confirmar emisión: Al presionar "Guardar y Emitir":
   - Si tiene pago seleccionado sin confirmar desglose → abre modal de pago
   - Si todo está validado → muestra modal de confirmación (#confirmEmitModal)
   - Modal muestra cliente, total, método de pago y desglose (si aplica)
   - Usuario confirma: "Sí, emitir" → envía formulario con emit=1
   - O cancela sin emitir
5. Imprimir PDF: incluye RUC, items, totales y desglose de pago

### Flujo de Caja
1. Abrir sesión: usuario ingresa monto inicial
2. Sistema registra apertura con timestamp
3. Durante la sesión: se asocian pagos de facturas a la caja
4. Cerrar sesión: usuario ingresa monto final
5. Sistema calcula diferencia y registra cierre

## Testing

### Unit Tests (PHPUnit/Pest)

**Coverage**: Core business logic validation including invoice calculations, payment validation, and cash session logic.

**Key Test Suites**:
- `tests/Unit/InvoiceTotalsCalculatorTest.php` - 7 tests validating invoice subtotal, tax, and total calculations
  - Single items with/without tax
  - Multiple items with mixed tax rates
  - Rounding precision (4 decimals internal, 2 decimals for persistence)
  - Edge cases: empty items, missing tax rates
  
- `tests/Feature/InvoiceControllerTest.php` - Integration tests for invoice CRUD operations
- `tests/Feature/CashSessionControllerTest.php` - Cash session workflow tests
- `tests/Feature/PaymentValidationTest.php` - Payment split validation tests

**Running Tests**:
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/InvoiceTotalsCalculatorTest.php

# Run with coverage
php artisan test --coverage
```

**Current Status**: 88 tests passing (290 assertions) ✅

### End-to-End Tests (Cypress)

**Location**: `cypress/e2e/CashSessionFlow.cy.js`

**Commands Available** (`cypress/support/commands.js`):
- `cy.login(email, password)` - Authenticate user
- `cy.openCashSession()` - Open cash session via UI
- `cy.navigateToCreateInvoice()` - Navigate to invoice creation
- `cy.selectCustomer(identification)` - Select customer from dropdown
- `cy.addInvoiceItem(productName, qty, price)` - Add item to invoice
- `cy.selectPaymentMethod(method)` - Select payment method (triggers modal)
- `cy.fillPaymentModal(cashAmount, transferAmount)` - Enter payment split
- `cy.modifyLastItemQuantity(newQuantity)` - Change item quantity (triggers reconfirmation)
- `cy.reconfirmPayment(cash, transfer)` - Handle payment reconfirmation after item changes
- `cy.submitInvoice(shouldEmit)` - Submit invoice form
- `cy.viewCashSummary()` - Open cash summary modal
- `cy.closeCashSession(reportedAmount)` - Close cash session

**Test Scenarios**:

1. **Complete Full Cash Session Flow** - Opens session, creates multi-item invoice with tax variations, enters mixed payment (cash+transfer), modifies item quantity triggering reconfirmation, closes session, validates totals match

2. **Payment Validation** - Attempts incorrect payment split, validates error message, corrects amount, confirms payment accepted

3. **Pending Invoices** - Creates invoice without selecting payment method, validates "pending" status, later selects payment method to complete

4. **Multiple Invoices** - Creates multiple invoices within single session, validates cumulative cash and transfer totals, confirms session totals reflect all invoices

**Running Cypress Tests**:

```bash
# Interactive mode (opens Cypress GUI)
npm run cypress:open

# Run all tests headless (CI/CD friendly)
npm run cypress:run

# Run specific test file
npm run cypress:run -- --spec cypress/e2e/CashSessionFlow.cy.js

# Run with specific browser
npm run cypress:run -- --browser chrome
```

**Test Credentials**:
- Email: `user@ecolindus.local`
- Password: `password`

**Dashboard Selectors**: 
Tests use `data-testid` attributes for reliable element targeting in the cash section:
- `data-testid="total-cash"` - Total cash amount display
- `data-testid="total-transfer"` - Total transfer amount display
- `data-testid="invoice-count"` - Invoice count in session
- `data-testid="total-amount"` - Total amount display

**CI/CD Integration**:
```yaml
# Example GitHub Actions workflow
- name: Run Cypress tests
  run: npm run cypress:run
```

## Tecnologías
- Laravel 12
- PostgreSQL
- Blade + Bootstrap 5
- Laravel Charts
- Git + GitHub
- PHPUnit/Pest (Unit Testing)
- Cypress (E2E Testing)