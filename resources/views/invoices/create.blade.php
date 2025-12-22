@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Nueva Factura</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-decoration-none">Facturas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nueva</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-dark">Volver a facturas</a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="invoiceForm" action="{{ route('invoices.store') }}" method="POST">
                @csrf

                <h5>Cliente</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Buscar cliente (cédula o apellido)</label>
                        <select id="customer-select" name="customer_id" class="form-select" style="width:100%"></select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="button" id="newCustomerBtn" class="btn btn-outline-secondary ms-auto">Agregar Cliente</button>
                    </div>
                </div>
                <div id="customer-details" class="row mb-3 d-none">
                    <div class="col-md-3"><label class="form-label">Identificación</label><input id="c_identification" name="customer[identification]" class="form-control" readonly /></div>
                    <div class="col-md-3"><label class="form-label">Nombre</label><input id="c_first_name" name="customer[first_name]" class="form-control" readonly /></div>
                    <div class="col-md-3"><label class="form-label">Apellido</label><input id="c_last_name" name="customer[last_name]" class="form-control" readonly /></div>
                    <div class="col-md-3"><label class="form-label">Teléfono</label><input id="c_phone" name="customer[phone]" class="form-control" readonly /></div>
                    <div class="col-md-3"><label class="form-label">Dirección</label><input id="c_address" name="customer[address]" class="form-control" readonly /></div>
                </div>

                <h5 class="mt-3">Artículos</h5>
                <div class="table-responsive">
                    <table class="table" id="items-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Impuesto</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(old('items'))
                                @foreach(old('items') as $it)
                                    <tr>
                                        <td>
                                            <select name="items[][product_id]" class="form-control product-select">
                                                @foreach($products as $p)
                                                    @php $pvp = number_format(($p->price ?? 0) * (1 + ($p->tax ?? 0)/100),2); @endphp
                                                    <option value="{{ $p->id }}" data-price="{{ $p->price ?? 0 }}" data-tax="{{ $p->tax ?? 0 }}" {{ (int)($it['product_id'] ?? 0) === $p->id ? 'selected' : '' }}>{{ $p->name }} - ${{ number_format($p->price ?? 0,2) }} (PVP: ${{ $pvp }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input name="items[][quantity]" value="{{ $it['quantity'] ?? 1 }}" class="form-control item-quantity" /></td>
                                        <td><input name="items[][unit_price]" value="{{ $it['unit_price'] ?? '' }}" class="form-control item-unit-price" /></td>
                                        <td>
                                            <select name="items[][tax_rate]" class="form-control">
                                                <option value="0" {{ (($it['tax_rate'] ?? '') === '0') ? 'selected' : '' }}>0%</option>
                                                <option value="15" {{ (($it['tax_rate'] ?? '') === '15') ? 'selected' : '' }}>15%</option>
                                            </select>
                                        </td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <button type="button" id="add-item" class="btn btn-sm btn-secondary">Agregar artículo</button>

                <div class="mt-2">
                    <label class="form-label">Buscar artículo por código de barras</label>
                    <div class="input-group">
                        <input id="barcodeSearch" class="form-control" placeholder="Escanea o escribe código" />
                        <button type="button" id="barcodeAddBtn" class="btn btn-outline-secondary">Agregar</button>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button class="btn btn-outline-secondary me-2" name="emit" value="0">Guardar como pendiente</button>
                    <button id="emitBtn" class="btn btn-dark" name="emit" value="1" disabled>Guardar y Emitir</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-link">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <template id="item-row">
        <tr>
            <td>
                <select name="items[][product_id]" class="form-control product-select">
                    @foreach($products as $p)
                        @php $pvp = number_format(($p->price ?? 0) * (1 + ($p->tax ?? 0)/100),2); @endphp
                        <option value="{{ $p->id }}" data-price="{{ $p->price ?? 0 }}" data-tax="{{ $p->tax ?? 0 }}">{{ $p->name }} - ${{ number_format($p->price ?? 0,2) }} (PVP: ${{ $pvp }})</option>
                    @endforeach
                </select>
            </td>
            <td><input name="items[][quantity]" value="1" class="form-control item-quantity" /></td>
            <td>
                <input name="items[][unit_price]" class="form-control item-unit-price" />
                <div class="form-text"><small>Con IVA: <span class="line-gross">0.00</span></small></div>
            </td>
            <td>
                <select name="items[][tax_rate]" class="form-control">
                    <option value="0">0%</option>
                    <option value="15">15%</option>
                </select>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
        </tr>
    </template>

    @push('scripts')
    <script>
    // Initialize Select2 for customer search
    $(document).ready(function(){
        $('#customer-select').select2({
            placeholder: 'Buscar por cédula o apellido',
            allowClear: true,
            ajax: {
                url: '{{ route('customers.search') }}',
                dataType: 'json',
                delay: 250,
                data: function(params){ return { q: params.term }; },
                processResults: function(data){ return { results: data.results }; }
            },
            minimumInputLength: 1
        }).on('select2:select', function(e){
            const data = e.params.data;
            // show detail fields
            $('#customer-details').removeClass('d-none');
            $('#c_identification').val(data.identification);
            $('#c_first_name').val(data.first_name);
            $('#c_last_name').val(data.last_name);
            $('#c_phone').val(data.phone);
            $('#c_address').val(data.address ?? '');
            // set a hidden input to send customer_id
            if(!$('#selected_customer_id').length){
                $('<input>').attr({type:'hidden', id:'selected_customer_id', name:'customer_id', value: data.id}).appendTo('#invoiceForm');
            } else { $('#selected_customer_id').val(data.id); }
        }).on('select2:clear', function(){
            $('#customer-details').addClass('d-none');
            $('#invoiceForm #selected_customer_id').remove();
        });

        // Add new customer button opens create modal
        $('#newCustomerBtn').on('click', function(){
            // open modal to create customer
            $('#customerModal').modal('show');
        });
    });

    // Handle submit from new customer modal and add to select2
    $(document).on('submit', '#customerCreateForm', function(e){
        e.preventDefault();
        const $form = $(this);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
                success: function(resp){
                const newOption = new Option(resp.text, resp.id, true, true);
                $('#customer-select').append(newOption).trigger('change');
                $('#customerModal').modal('hide');
                if(!$('#invoiceForm #selected_customer_id').length){
                    $('<input>').attr({type:'hidden', id:'selected_customer_id', name:'customer_id', value: resp.id}).appendTo('#invoiceForm');
                } else { $('#selected_customer_id').val(resp.id); }
                // set details
                $('#customer-details').removeClass('d-none');
                $('#c_identification').val(resp.customer.identification);
                $('#c_first_name').val(resp.customer.first_name);
                $('#c_last_name').val(resp.customer.last_name);
                    $('#c_phone').val(resp.customer.phone);
                    $('#c_address').val(resp.customer.address ?? '');
            },
            error: function(xhr){
                // Clear previous errors
                ['identification','first_name','last_name','phone','email','address'].forEach(function(f){
                    $('#customer_' + f).removeClass('is-invalid');
                    $('#customer_' + f + '_error').text('');
                });

                if(xhr.status === 409){
                    // customer exists — select it
                    const resp = xhr.responseJSON;
                    const customer = resp.customer;
                    const newOption = new Option(customer.first_name + ' ' + customer.last_name + ' - ' + customer.identification, customer.id, true, true);
                    $('#customer-select').append(newOption).trigger('change');
                    $('#customerModal').modal('hide');
                    $('#customer-details').removeClass('d-none');
                    $('#c_identification').val(customer.identification);
                    $('#c_first_name').val(customer.first_name);
                    $('#c_last_name').val(customer.last_name);
                    $('#c_phone').val(customer.phone);
                    $('#c_address').val(customer.address ?? '');
                    if(!$('#selected_customer_id').length){ $('<input>').attr({type:'hidden', id:'selected_customer_id', name:'customer_id', value: customer.id}).appendTo('#invoiceForm'); } else { $('#selected_customer_id').val(customer.id); }
                } else if(xhr.status === 422){
                    // Validation errors
                    const errors = xhr.responseJSON?.errors || {};
                    Object.keys(errors).forEach(function(key){
                        const el = $('#customer_' + key.replace('customer.','').replace(/\[|\]/g,'').replace('.','_'));
                        if(el.length){
                            el.addClass('is-invalid');
                            $('#customer_' + key.replace('customer.','').replace(/\[|\]/g,'').replace('.','_') + '_error').text(errors[key][0]);
                        }
                    });
                } else {
                    alert('Error al crear cliente: ' + (xhr.responseJSON?.message || xhr.statusText));
                }
            }
        });
    });
    document.addEventListener('DOMContentLoaded', function(){
        const addBtn = document.getElementById('add-item');
        const tbody = document.querySelector('#items-table tbody');
        const template = document.getElementById('item-row').content;

        function attachRowListeners(row){
            const select = row.querySelector('.product-select');
            const qty = row.querySelector('.item-quantity');
            const price = row.querySelector('.item-unit-price');
            const tax = row.querySelector('select[name="items[][tax_rate]"]');

            function updateUnitPrice(){
                let p = 0;
                let defaultTax = null;
                const selected = select.selectedOptions && select.selectedOptions[0];
                if(selected){
                    p = parseFloat(selected.dataset.price || 0);
                    defaultTax = selected.dataset.tax ?? null;
                } else {
                    const selData = $(select).select2('data')[0];
                    if(selData){ p = parseFloat(selData.price || 0); defaultTax = selData.tax ?? null; }
                }
                price.value = (isNaN(p) ? 0 : p).toFixed(2);
                // Only set default tax on create if there's no explicit value set by the user
                if(defaultTax !== null && tax && (tax.value === '' || tax.value === null || typeof tax.value === 'undefined')){
                    // set select value safely and trigger change for select2 if present
                    try {
                        if (window.jQuery && $(tax).hasClass('select2-hidden-accessible')) {
                            $(tax).val(defaultTax).trigger('change');
                        } else {
                            tax.value = defaultTax;
                        }
                    } catch (e) { tax.value = defaultTax; }
                }
                updateTotals();
            }

            function updateTotals(){
                computeGlobalTotals();
            }

            select.addEventListener('change', updateUnitPrice);
            // merge duplicate product selection: increase quantity of the existing row and remove this row
            $(select).on('select2:select', function(e){
                const pid = select.value;
                if (!pid) return;
                // find other row with same pid
                let found = null;
                document.querySelectorAll('#invoiceForm #items-table tbody tr').forEach(function(r){
                    if (r === row) return; // skip the current
                    const otherSel = r.querySelector('.product-select');
                    if (otherSel && otherSel.value === pid) { found = r; }
                });
                if (found) {
                    const otherQtyInput = found.querySelector('.item-quantity');
                    const currQty = parseFloat(otherQtyInput.value || 0);
                    const thisQtyInput = row.querySelector('.item-quantity');
                    otherQtyInput.value = (currQty + (parseFloat(thisQtyInput.value || 0))).toFixed(2);
                    // Preserve or prefer explicit tax selection from the new row
                    const thisTaxSel = row.querySelector('select[name="items[][tax_rate]"]');
                    const otherTaxSel = found.querySelector('select[name="items[][tax_rate]"]');
                    let thisTax = null;
                    if(thisTaxSel){ thisTax = thisTaxSel.value; }
                    if(typeof thisTax !== 'undefined' && thisTax !== null && thisTax !== ''){
                        if(otherTaxSel){
                            try{
                                if(window.jQuery && $(otherTaxSel).hasClass('select2-hidden-accessible')){
                                    $(otherTaxSel).val(thisTax).trigger('change');
                                } else { otherTaxSel.value = thisTax; }
                            } catch(e){ otherTaxSel.value = thisTax; }
                        }
                    }
                    // If other row's unit price is empty, prefer this row's unit price
                    const otherPriceInput = found.querySelector('.item-unit-price');
                    const thisPriceInput = row.querySelector('.item-unit-price');
                    if(otherPriceInput && (!otherPriceInput.value || otherPriceInput.value === '')){
                        otherPriceInput.value = thisPriceInput.value || otherPriceInput.value;
                    }
                    // remove the current row
                    row.remove();
                    computeGlobalTotals();
                    validateEmitButton();
                }
            });
            $(select).on('select2:select', updateUnitPrice);
            qty.addEventListener('input', updateTotals);
            price.addEventListener('input', updateTotals);
            tax.addEventListener('change', updateTotals);

            updateUnitPrice();
        }

        addBtn.addEventListener('click', ()=>{
            const clone = document.importNode(template, true);
            tbody.appendChild(clone);
            // Activate select2 on the newly added select
                const selectEl = $(tbody.lastElementChild).find('.product-select');
                if(!selectEl.hasClass('select2-hidden-accessible')){
                    selectEl.select2({
                ajax: {
                    url: '{{ route('products.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params){ return { q: params.term }; },
                    processResults: function(data){ return { results: data.results }; }
                },
                minimumInputLength: 0,
                allowClear: true
                    });
                }
            // validate button on row change
            attachRowListeners(tbody.lastElementChild);
            validateEmitButton();
        });

        // Barcode search add
        $('#barcodeAddBtn').on('click', function(){
            const code = $('#barcodeSearch').val().trim();
            if(!code) return;
            $.get('{{ route('products.search') }}', { q: code }, function(resp){
                if(resp.results && resp.results.length){
                    const pid = resp.results[0].id;
                    // add new row and select product
                    addBtn.click();
                    const lastRow = tbody.lastElementChild;
                    const sel = lastRow.querySelector('.product-select');
                    $(sel).val(pid).trigger('change');
                } else {
                    alert('Producto no encontrado con ese código');
                }
            }).fail(function(){ alert('Error al buscar producto'); });
        });

        tbody.addEventListener('click', function(e){
            if(e.target.classList.contains('remove-row')){
                e.target.closest('tr').remove();
                computeGlobalTotals();
            }
        });

        function computeGlobalTotals(){
            let subtotal = 0;
            let taxTotal = 0;

            document.querySelectorAll('#invoiceForm #items-table tbody tr').forEach(function(row){
                const qty = parseFloat(row.querySelector('.item-quantity').value || 0);
                const unit = parseFloat(row.querySelector('.item-unit-price').value || 0);
                const taxRate = parseFloat(row.querySelector('select[name="items[][tax_rate]"]').value || 0);
                const line = qty * unit;
                subtotal += line;
                taxTotal += (taxRate/100) * line;
                // update line gross display (unit price * (1 + tax))
                const grossEl = row.querySelector('.line-gross');
                if(grossEl){
                    const gross = unit * (1 + (taxRate/100));
                    grossEl.textContent = gross.toFixed(2);
                }
            });

            const total = subtotal + taxTotal;

            let summary = document.getElementById('invoice-summary');
            if(!summary){
                summary = document.createElement('div');
                summary.id = 'invoice-summary';
                summary.className = 'mt-3 text-end';
                summary.setAttribute('role', 'status');
                summary.setAttribute('aria-live', 'polite');
                document.querySelector('#invoiceForm').appendChild(summary);
            }

            summary.innerHTML = `<p>Subtotal: ${subtotal.toFixed(2)}</p><p>Impuesto: ${taxTotal.toFixed(2)}</p><h4>Total: ${total.toFixed(2)}</h4>`;
        }

            // initialize select2 on any pre-existing product-select selects
        $('.product-select').each(function(){
            if(!$(this).hasClass('select2-hidden-accessible')){
                $(this).select2({
            ajax: {
                url: '{{ route('products.search') }}',
                dataType: 'json',
                delay: 250,
                data: function(params){ return { q: params.term }; },
                processResults: function(data){ return { results: data.results }; }
            },
            minimumInputLength: 0,
            allowClear: true
                });
            }
        });

        // add initial row only if there are no pre-existing rows (e.g. old() values after validation)
        if (document.querySelectorAll('#invoiceForm #items-table tbody tr').length === 0) {
            addBtn.click();
        }

        // wire up customer modal submit button
        $('#customerCreateSubmit').on('click', function(){ $('#customerCreateForm').submit(); });

        // On form submit, remove any blank item rows where no product is selected
            $('#invoiceForm').on('submit', function(){
                // Re-index items to ensure tax_rate is sent even when 0 and preserve index mapping
                const rows = $('#invoiceForm #items-table tbody tr');
                rows.each(function(i){
                    $(this).find('select[name="items[][product_id]"]').attr('name', `items[${i}][product_id]`);
                    $(this).find('input[name="items[][quantity]"]').attr('name', `items[${i}][quantity]`);
                    $(this).find('input[name="items[][unit_price]"]').attr('name', `items[${i}][unit_price]`);
                    const taxSel = $(this).find('select[name="items[][tax_rate]"]');
                    if(taxSel.length){ taxSel.attr('name', `items[${i}][tax_rate]`); }
                });
                // Debug - log tax_rate values in the form payload (temporary)
                try {
                    const taxes = [];
                    $('#invoiceForm #items-table tbody tr').each(function(){
                        const taxVal = $(this).find('select[name="items[][tax_rate]"]').val();
                        taxes.push(taxVal);
                    });
                    console.log('DEBUG invoiceForm tax_rate values:', taxes);
                } catch (e) { console.log('DEBUG invoiceForm tax_rate logging error', e); }
                // Client-side validation: ensure either a selected customer id or an identification value is present
                const hasCid = $('#invoiceForm #selected_customer_id').length > 0;
                const identificationVal = ($('#c_identification').val() || '').trim();
                if (!hasCid && identificationVal === '') {
                    showGlobalToast('Debe seleccionar o crear un cliente con identificación', {classname: 'bg-danger text-white', delay: 2500});
                    return false;
                }
            $('#invoiceForm #items-table tbody tr').each(function(){
                const sel = $(this).find('.product-select');
                const pid = sel.val();
                if(!pid || pid === null || pid === ''){ $(this).remove(); }
            });
            // attach listeners to update emit button state
            $('#invoiceForm .product-select, #invoiceForm .item-quantity').on('change input', function(){ validateEmitButton(); });
            // Prevent sending items with zero or invalid quantities
            let invalid = false;
            // Ensure unit_price set if product selected but left empty (pull price from data attribute or select2 data)
            $('#invoiceForm #items-table tbody tr').each(function(){
                const sel = $(this).find('.product-select');
                const priceInput = $(this).find('.item-unit-price');
                const pid = sel.val();
                if(pid && (!priceInput.val() || priceInput.val() === '')){
                    let p = parseFloat(sel.find('option:selected').data('price') || 0);
                    if(!p){
                        const sd = sel.select2('data')[0];
                        if(sd && sd.price){
                            p = sd.price;
                        }
                    }
                    priceInput.val((isNaN(p) ? 0 : p).toFixed(2));
                }
            });
            $('#invoiceForm #items-table tbody tr').each(function(){
                const qty = parseFloat($(this).find('.item-quantity').val() || 0);
                if(isNaN(qty) || qty <= 0){ invalid = true; }
            });
            if(invalid){
                alert('Revise las cantidades: todos los artículos deben tener una cantidad mayor a 0');
                return false;
            }
        });

        function validateEmitButton(){
            let ok = false;
            $('#invoiceForm #items-table tbody tr').each(function(){
                const sel = $(this).find('.product-select');
                const pid = sel.val();
                const qty = parseFloat($(this).find('.item-quantity').val() || 0);
                if(pid && pid !== '' && !isNaN(qty) && qty > 0){ ok = true; return false; }
            });
            $('#emitBtn').prop('disabled', !ok);
        }
    });
    </script>
    @endpush

    <!-- Customer Create Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Crear Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="customerCreateForm" action="{{ route('customers.store') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Identificación</label>
                            <input id="customer_identification" name="identification" aria-describedby="customer_identification_error" aria-required="true" class="form-control" required>
                            <div class="invalid-feedback" id="customer_identification_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input id="customer_first_name" name="first_name" aria-describedby="customer_first_name_error" aria-required="true" class="form-control" required>
                            <div class="invalid-feedback" id="customer_first_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido</label>
                            <input id="customer_last_name" name="last_name" aria-describedby="customer_last_name_error" aria-required="true" class="form-control" required>
                            <div class="invalid-feedback" id="customer_last_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input id="customer_phone" name="phone" aria-describedby="customer_phone_error" class="form-control">
                            <div class="invalid-feedback" id="customer_phone_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input id="customer_email" name="email" aria-describedby="customer_email_error" class="form-control" type="email">
                            <div class="invalid-feedback" id="customer_email_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <textarea id="customer_address" name="address" aria-describedby="customer_address_error" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback" id="customer_address_error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-dark" id="customerCreateSubmit">Crear</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
