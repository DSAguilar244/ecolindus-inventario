describe('Invoice Admin Actions E2E', () => {
  before(() => {
    // create an admin user and a product + invoice via artisan tinker
    const createScript = `
    $admin = App\\Models\\User::factory()->create(['email' => 'admin@local', 'password' => bcrypt('password'), 'is_admin' => true]);
    $product = App\\Models\\Product::factory()->create(['name' => 'E2E-Admin', 'price' => 50, 'stock' => 10]);
    $invoice = App\\Models\\Invoice::factory()->create(['user_id' => $admin->id, 'status' => App\\Models\\Invoice::STATUS_EMITIDA, 'customer_id' => App\\Models\\Customer::factory()->create()->id]);
    App\\Models\\InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 50, 'line_total' => 100]);
    // simulate stock decrement
    $product->decrement('stock', 2);
    echo json_encode(['admin' => $admin->email, 'product_id' => $product->id, 'invoice_id' => $invoice->id]);
    `;

    cy.exec(`php artisan tinker --execute "${createScript.replace(/\n/g, '')}"`, { timeout: 60000 }).then((res) => {
      const out = JSON.parse(res.stdout);
      cy.wrap(out).as('fixture');
    });
  });

  it('admin sees edit and delete buttons and can download PDF and delete invoice restoring stock', function() {
    const adminEmail = 'admin@local';
    const adminPass = 'password';

    // Login as admin
    cy.visit('/login');
    cy.get('input[name=email]').type(adminEmail);
    cy.get('input[name=password]').type(adminPass);
    cy.get('button[type=submit]').click();
    cy.url().should('include', '/dashboard');

    // Visit invoices index
    cy.visit('/invoices');
    cy.get('@fixture').then((f) => {
      const invoiceId = f.invoice_id;
      cy.visit(`/invoices/${invoiceId}`);

      // Should see Edit (admin)
      cy.contains('Editar (admin)').should('exist');

      // Should see Permanent delete button
      cy.contains('Eliminar Permanentemente').should('exist');

      // Print PDF button
      cy.contains('Imprimir / PDF').should('exist');
      // Test PDF generation by requesting the route directly
      cy.request({ url: `/invoices/${invoiceId}/print`, encoding: 'binary' }).then((resp) => {
        expect(resp.status).to.equal(200);
        expect(resp.headers['content-type']).to.include('application/pdf');
      });

      // Read product stock via php artisan tinker
      cy.exec(`php artisan tinker --execute "echo App\\Models\\Product::find(${f.product_id})->stock;"`).then((r) => {
        expect(parseInt(r.stdout)).to.equal(8); // 10 initial - 2 emitted
      });

      // Perform Permanent Delete: open modal and submit
      // The delete modal contains a textarea for reason and a confirm button that will submit to the route
      cy.get('button').contains('Eliminar Permanentemente').click();
      cy.get('textarea[name=audit_reason]').type('E2E test deletion reason');
      cy.get('button').contains('Eliminar Permanentemente').last().click();

      // After deletion, product stock should be restored
      cy.exec(`php artisan tinker --execute "echo App\\Models\\Product::find(${f.product_id})->stock;"`).then((r) => {
        expect(parseInt(r.stdout)).to.equal(10);
      });
    });
  });

  it('non-admin does not see admin-only edit and delete buttons', () => {
    // Create normal user and invoice
    const setup = `
      $user = App\\Models\\User::factory()->create(['email'=>'user@local','password'=>bcrypt('password')]);
      $product = App\\Models\\Product::factory()->create(['name' => 'E2E-User', 'price' => 20, 'stock' => 5]);
      $invoice = App\\Models\\Invoice::factory()->create(['user_id' => $user->id, 'status' => App\\Models\\Invoice::STATUS_EMITIDA, 'customer_id' => App\\Models\\Customer::factory()->create()->id]);
      App\\Models\\InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 20, 'line_total' => 20]);
      $product->decrement('stock', 1);
      echo json_encode(['user' => $user->email, 'invoice_id' => $invoice->id, 'product_id' => $product->id]);
    `;
    cy.exec(`php artisan tinker --execute "${setup.replace(/\n/g, '')}"`, { timeout: 60000 }).then((res) => {
      const out = JSON.parse(res.stdout);
      // Login as normal user
      cy.visit('/login');
      cy.get('input[name=email]').type(out.user);
      cy.get('input[name=password]').type('password');
      cy.get('button[type=submit]').click();
      cy.url().should('include', '/dashboard');

      cy.visit(`/invoices/${out.invoice_id}`);
      // Should not see edit admin or permanent delete
      cy.contains('Editar (admin)').should('not.exist');
      cy.contains('Eliminar Permanentemente').should('not.exist');
    });
  });
});
