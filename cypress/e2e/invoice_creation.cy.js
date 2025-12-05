describe('Invoice creation E2E', () => {
  it('creates an invoice using select2 product selection and emits', () => {
    // Login using the dev user
    cy.visit('/login');
    cy.get('input[name=email]').type('dev@local');
    cy.get('input[name=password]').type('password');
    cy.get('button[type=submit]').click();
    cy.url().should('include', '/dashboard');

    // Create a product via UI
    cy.visit('/products/create');
    cy.get('input[name=name]').type('E2E Agua Test');
    cy.get('input[name=code]').type('E2E-01');
    cy.get('input[name=unit]').type('L');
    cy.get('input[name=price]').type('3');
    // use 15% product default
    cy.get('select[name=tax]').select('15');
    cy.get('button[type=submit]').contains('Guardar').click();

    // Now create an invoice
    cy.visit('/invoices/create');
    // Add a row (should be already present)
    cy.get('select.product-select').first().then($sel => {
      // find the option value for the product we just created
      cy.get($sel).find('option').contains('E2E Agua Test').invoke('val').then((val) => {
        cy.wrap($sel).select(val);
      });
    });
    cy.get('.item-quantity').first().clear().type('2');
    // Set the item tax to 0 explicitly in the UI after product selection
    cy.get('select[name="items[][tax_rate]"]').first().select('0');
    // Intercept POST to /invoices and assert tax_rate is sent as '0'
    cy.intercept('POST', '/invoices', (req) => {
      const items = req.body.items || [];
      expect(items[0].tax_rate).to.eq('0');
    }).as('postInvoice');

    // Emit button must be enabled
    cy.get('#emitBtn').should('not.be.disabled').click();
    cy.wait('@postInvoice');

    // After submit, user should be redirected to the invoice show page
    cy.url().should('match', /\/invoices\/[0-9]+$/);
    cy.contains('E2E Agua Test');
  });
  
  it('edits an invoice and sets tax to 0%', () => {
    cy.url().then((url) => {
      // extract invoice id from URL
      const matches = url.match(/\/invoices\/(\d+)/);
      if (!matches) return;
      const invoiceId = matches[1];
      cy.visit(`/invoices/${invoiceId}/edit`);

      // Set tax to 0 in the edit form
      cy.get('select[name="items[][tax_rate]"]').first().select('0');

      // intercept PUT and ensure payload includes tax_rate = '0'
      cy.intercept('PUT', `/invoices/${invoiceId}`, (req) => {
        const items = req.body.items || [];
        expect(items[0].tax_rate).to.eq('0');
      }).as('putInvoice');

      cy.get('#emitBtn').should('not.be.disabled').click();
      cy.wait('@putInvoice');

      // Ensure the show page reflects the tax and total is updated
      cy.url().should('match', new RegExp(`/invoices/${invoiceId}$`));
      cy.contains('Impuesto: 0.00');
    });
  });

  it('disables emit when quantity is 0 and prevents submission', () => {
    cy.visit('/invoices/create');
    cy.get('select.product-select').first().then($sel => {
      cy.get($sel).find('option').contains('E2E Agua Test').invoke('val').then((val) => {
        cy.wrap($sel).select(val);
      });
    });
    cy.get('.item-quantity').first().clear().type('0');
    cy.get('#emitBtn').should('be.disabled');
  });

  it('auto-fills price on submit when unit_price is blank and product selected', () => {
    cy.visit('/invoices/create');
    cy.get('select.product-select').first().then($sel => {
      cy.get($sel).find('option').contains('E2E Agua Test').invoke('val').then((val) => {
        cy.wrap($sel).select(val);
      });
    });
    // clear price
    cy.get('.item-unit-price').first().clear();
    // ensure emit button is enabled (quantity default is 1)
    cy.get('#emitBtn').should('not.be.disabled');
    cy.get('#emitBtn').click();
    cy.url().should('match', /\/invoices\/[0-9]+$/);
    cy.contains('E2E Agua Test');
  });
});
