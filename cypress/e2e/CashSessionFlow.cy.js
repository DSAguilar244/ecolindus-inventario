// cypress/e2e/CashSessionFlow.cy.js
/**
 * Cash Session and Invoice Payment Flow Integration Test
 * 
 * This test validates the complete flow of:
 * 1. Opening a cash session
 * 2. Creating an invoice with mixed payment (cash + transfer)
 * 3. Confirming payment amounts in modal
 * 4. Modifying invoice items and being forced to reconfirm payment
 * 5. Closing the cash session and verifying totals
 */

describe('Cash Session and Invoice Payment Flow', () => {
  beforeEach(() => {
    // Login before each test
    cy.login('test@example.com', 'password');
    cy.wait(500);
  });

  it('should complete full cash session flow with mixed payment invoices', () => {
    // Step 1: Open cash session
    cy.openCashSession();
    cy.wait(500);

    // Step 2: Navigate to create invoice
    cy.navigateToCreateInvoice();
    
    // Step 3: Select customer
    cy.selectCustomer('1234567890'); // Use a test customer ID

    // Step 4: Add invoice items
    // Add item 1: quantity 2, price 50
    cy.addInvoiceItem('Test Product', 2, 50);
    
    // Add item 2: quantity 1, price 30
    cy.addInvoiceItem('Another Product', 1, 30);

    // Wait for totals to calculate
    cy.wait(500);

    // Step 5: Verify subtotal is calculated (2*50 + 1*30 = 130)
    cy.get('#invoice-summary').should('be.visible');
    cy.get('#invoice-summary').should('contain', '130');

    // Step 6: Select mixed payment method
    cy.selectPaymentMethod('Pago físico');

    // Step 7: Fill payment modal with mixed amounts
    // Total should be ~130 (no tax in test products typically)
    const cashAmount = 80;
    const transferAmount = 50;
    cy.fillPaymentModal(cashAmount, transferAmount);

    // Wait for payment to be saved
    cy.wait(300);

    // Verify that the payment inputs are now hidden (saved)
    cy.get('input[name="cash_amount"]').should('have.value', cashAmount);
    cy.get('input[name="transfer_amount"]').should('have.value', transferAmount);

    // Step 8: Modify last item quantity (should invalidate payment)
    cy.modifyLastItemQuantity(2); // Change from 1 to 2
    
    // Step 9: Verify payment changed modal appears
    cy.get('#paymentChangedModal').should('be.visible');
    cy.contains('Se detectaron cambios en los artículos').should('be.visible');

    // Step 10: Reconfirm payment with new amounts (new total: 2*50 + 2*30 = 160)
    const newCashAmount = 100;
    const newTransferAmount = 60;
    cy.reconfirmPayment(newCashAmount, newTransferAmount);

    // Verify new payment is saved
    cy.get('input[name="cash_amount"]').should('have.value', newCashAmount);
    cy.get('input[name="transfer_amount"]').should('have.value', newTransferAmount);

    // Step 11: Submit invoice
    cy.submitInvoice(true); // Emit invoice

    // Wait for invoice to be created
    cy.wait(500);

    // Step 12: Verify invoice was created with correct payment info
    cy.url().should('include', '/invoices/');
    cy.get('body').should('contain', 'Efectivo (cash_amount)');
    cy.get('body').should('contain', newCashAmount.toFixed(2));
    cy.get('body').should('contain', newTransferAmount.toFixed(2));

    // Step 13: View cash session summary
    cy.viewCashSummary();

    // Step 14: Verify summary shows correct totals
    // Total cash = newCashAmount
    // Total transfer = newTransferAmount
    // Total = newCashAmount + newTransferAmount
    cy.get('#cashSummaryModal').within(() => {
      cy.contains('Efectivo').should('be.visible');
      cy.contains('Transferencia').should('be.visible');
      // Verify totals are displayed
      cy.get('[data-testid="total-cash"]').should('contain', newCashAmount.toFixed(2));
      cy.get('[data-testid="total-transfer"]').should('contain', newTransferAmount.toFixed(2));
    });

    // Step 15: Close cash session with exact amount (no discrepancy)
    cy.closeCashSession((newCashAmount + newTransferAmount).toFixed(2));

    // Verify cash session is closed
    cy.contains('Caja cerrada correctamente').should('be.visible');
  });

  it('should reject payment if cash + transfer does not equal invoice total', () => {
    cy.openCashSession();
    cy.navigateToCreateInvoice();
    cy.selectCustomer('1234567890');
    
    // Add single item
    cy.addInvoiceItem('Test Product', 1, 100);
    cy.wait(500);

    cy.selectPaymentMethod('Pago físico');

    // Try to fill with mismatched amounts (e.g., 60 + 40 = 100, but total is 100)
    // First attempt with wrong total
    cy.get('#payment_cash').clear().type(50);
    cy.get('#payment_transfer').clear().type(40);
    cy.contains('button', 'Guardar pago').click();

    // Should see error message
    cy.get('#payment_error').should('not.have.class', 'd-none');
    cy.contains('suma de efectivo y transferencia debe ser igual').should('be.visible');

    // Correct the amounts
    cy.get('#payment_cash').clear().type(100);
    cy.get('#payment_transfer').clear().type(0);
    cy.contains('button', 'Guardar pago').click();

    // Modal should close
    cy.get('#paymentModal').should('not.be.visible');
  });

  it('should allow pending invoice creation without payment', () => {
    cy.openCashSession();
    cy.navigateToCreateInvoice();
    cy.selectCustomer('1234567890');
    
    cy.addInvoiceItem('Test Product', 1, 50);
    cy.wait(500);

    // Don't select payment method, just save as pending
    cy.contains('button', 'Guardar como pendiente').click();

    cy.contains('Factura creada correctamente').should('be.visible');
    cy.url().should('include', '/invoices/');
  });

  it('should maintain cash totals across multiple invoices in session', () => {
    cy.openCashSession();

    // First invoice: 80 cash, 20 transfer
    cy.navigateToCreateInvoice();
    cy.selectCustomer('1234567890');
    cy.addInvoiceItem('Product A', 1, 100);
    cy.wait(300);
    cy.selectPaymentMethod('Pago físico');
    cy.fillPaymentModal(80, 20);
    cy.submitInvoice(true);
    cy.wait(500);

    // Second invoice: 60 cash, 40 transfer
    cy.navigateToCreateInvoice();
    cy.selectCustomer('0987654321');
    cy.addInvoiceItem('Product B', 1, 100);
    cy.wait(300);
    cy.selectPaymentMethod('Pago físico');
    cy.fillPaymentModal(60, 40);
    cy.submitInvoice(true);
    cy.wait(500);

    // View summary: should show totals = 140 cash, 60 transfer
    cy.viewCashSummary();
    cy.get('#cashSummaryModal').within(() => {
      cy.get('[data-testid="total-cash"]').should('contain', '140.00');
      cy.get('[data-testid="total-transfer"]').should('contain', '60.00');
    });

    // Close session
    cy.closeCashSession('200.00');
    cy.contains('Caja cerrada correctamente').should('be.visible');
  });
});
