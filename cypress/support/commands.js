// cypress/support/commands.js

// Login command
Cypress.Commands.add('login', (email = 'test@example.com', password = 'password') => {
  cy.visit('/login');
  cy.get('input[name="email"]').type(email);
  cy.get('input[name="password"]').type(password);
  cy.get('button[type="submit"]').click();
  cy.url().should('include', '/dashboard');
});

// Open cash session
Cypress.Commands.add('openCashSession', () => {
  cy.visit('/dashboard');
  cy.contains('button', 'Abrir Caja').click();
  cy.get('[role="dialog"]').should('be.visible');
  cy.contains('button', 'Confirmar').click();
  cy.contains('Caja abierta correctamente').should('be.visible');
});

// Navigate to create invoice
Cypress.Commands.add('navigateToCreateInvoice', () => {
  cy.visit('/invoices/create');
  cy.get('#invoiceForm').should('be.visible');
});

// Select customer by identification
Cypress.Commands.add('selectCustomer', (identification) => {
  cy.get('#customer-select').click();
  cy.get('.select2-search__field').type(identification);
  cy.get('.select2-results__option').first().click();
  cy.get('#c_identification').should('have.value', identification);
});

// Add invoice item (product, quantity, price)
Cypress.Commands.add('addInvoiceItem', (productName, quantity, price) => {
  cy.get('#add-item').click();
  cy.get('tbody tr').last().find('.product-select').click();
  cy.get('.select2-search__field').type(productName);
  cy.get('.select2-results__option').first().click();
  cy.get('tbody tr').last().find('.item-quantity').clear().type(quantity);
  cy.get('tbody tr').last().find('.item-unit-price').clear().type(price);
  // Wait for totals to recalculate
  cy.wait(300);
});

// Select payment method
Cypress.Commands.add('selectPaymentMethod', (method) => {
  cy.get('select[name="payment_method"]').select(method);
  // Modal should open automatically
  cy.get('#paymentModal').should('be.visible');
});

// Fill payment modal
Cypress.Commands.add('fillPaymentModal', (cashAmount, transferAmount) => {
  cy.get('#payment_cash').clear().type(cashAmount);
  cy.get('#payment_transfer').clear().type(transferAmount);
  cy.contains('button', 'Guardar pago').click();
  cy.get('#paymentModal').should('not.be.visible');
});

// Modify last invoice item quantity
Cypress.Commands.add('modifyLastItemQuantity', (newQuantity) => {
  cy.get('tbody tr').last().find('.item-quantity').clear().type(newQuantity);
  cy.wait(300); // Wait for recalculation and payment invalidation
});

// Reconfirm payment in modal
Cypress.Commands.add('reconfirmPayment', (cashAmount, transferAmount) => {
  cy.get('#paymentChangedModal').should('be.visible');
  cy.contains('button', 'Reconfirmar pago').click();
  cy.get('#paymentModal').should('be.visible');
  cy.get('#payment_cash').clear().type(cashAmount);
  cy.get('#payment_transfer').clear().type(transferAmount);
  cy.contains('button', 'Guardar pago').click();
  cy.get('#paymentModal').should('not.be.visible');
});

// Submit invoice form
Cypress.Commands.add('submitInvoice', (shouldEmit = true) => {
  if (shouldEmit) {
    cy.contains('button', 'Guardar y Emitir').click();
  } else {
    cy.contains('button', 'Guardar como pendiente').click();
  }
  cy.contains('Factura creada correctamente').should('be.visible');
});

// View cash session summary
Cypress.Commands.add('viewCashSummary', () => {
  cy.visit('/dashboard');
  cy.contains('button', 'Ver Resumen').click();
  cy.get('#cashSummaryModal').should('be.visible');
});

// Close cash session with reported amount
Cypress.Commands.add('closeCashSession', (reportedAmount) => {
  cy.visit('/dashboard');
  cy.contains('button', 'Cerrar Caja').click();
  cy.get('#closeConfirmModal').should('be.visible');
  if (reportedAmount) {
    cy.get('input[name="reported_closing_amount"]').type(reportedAmount);
  }
  cy.contains('button', 'Confirmar Cierre').click();
  cy.contains('Caja cerrada correctamente').should('be.visible');
});
