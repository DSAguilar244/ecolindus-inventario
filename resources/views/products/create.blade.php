@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Nuevo Producto</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Productos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Create Form Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-box-seam me-2"></i>Información del Producto
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.store') }}" method="POST" id="createForm">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nombre del producto</label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id" class="form-label">Categoría</label>
                                    <div class="input-group">
                                        <select name="category_id" id="category_id" 
                                                class="form-select @error('category_id') is-invalid @enderror">
                                            <option value="">Seleccione una categoría</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="openCategoryModal" title="Crear categoría">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand_id" class="form-label">Marca</label>
                                    <div class="input-group">
                                        <select name="brand_id" id="brand_id" class="form-select">
                                            <option value="">Sin marca</option>
                                            @foreach($brands as $b)
                                                <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="openBrandModal" title="Crear marca">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit" class="form-label">Unidad de medida</label>
                                    <select name="unit" id="unit" 
                                            class="form-select @error('unit') is-invalid @enderror" required>
                                        <option value="">Seleccione una unidad</option>
                                        <option value="unidades" {{ old('unit') == 'unidades' ? 'selected' : '' }}>Unidades</option>
                                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogramos</option>
                                        <option value="litros" {{ old('unit') == 'litros' ? 'selected' : '' }}>Litros</option>
                                        <option value="metros" {{ old('unit') == 'metros' ? 'selected' : '' }}>Metros</option>
                                        <option value="cajas" {{ old('unit') == 'cajas' ? 'selected' : '' }}>Cajas</option>
                                    </select>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code" class="form-label">Código</label>
                                    <input type="text" name="code" id="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code') }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price" class="form-label">Precio</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="price" id="price" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               value="{{ old('price', 0) }}" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="stock" class="form-label">Stock inicial</label>
                                    <input type="number" name="stock" id="stock" 
                                           class="form-control @error('stock') is-invalid @enderror" 
                                           value="{{ old('stock', 0) }}" min="0">
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="min_stock" class="form-label">Stock mínimo</label>
                                    <input type="number" name="min_stock" id="min_stock" 
                                           class="form-control @error('min_stock') is-invalid @enderror" 
                                           value="{{ old('min_stock', 0) }}" min="0">
                                    @error('min_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tax_rate" class="form-label">Impuesto</label>
                                    <select name="tax_rate" id="tax_rate" class="form-select">
                                        <option value="0" {{ old('tax_rate') == '0' ? 'selected' : '' }}>0%</option>
                                        <option value="15" {{ old('tax_rate') == '15' ? 'selected' : '' }}>15%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-light">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" form="createForm" class="btn btn-dark">
                            <i class="bi bi-check-circle me-2"></i>Crear Producto
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Information Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Información Importante
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border-0 mb-4">
                        <h6 class="alert-heading fw-bold mb-2">
                            <i class="bi bi-lightbulb me-2"></i>Consejos
                        </h6>
                        <ul class="mb-0 ps-3">
                            <li>Use nombres descriptivos y únicos para los productos</li>
                            <li>Seleccione una unidad de medida apropiada</li>
                            <li>Establezca un stock mínimo para recibir alertas</li>
                            <li>El stock inicial puede ser 0 si aún no tiene inventario</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-dark" onclick="showUnitGuide()">
                            <i class="bi bi-rulers me-2"></i>Guía de Unidades
                        </a>
                        <a href="#" class="btn btn-outline-dark" onclick="showCategoryGuide()">
                            <i class="bi bi-tags me-2"></i>Guía de Categorías
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unit Guide Modal -->
<div class="modal fade" id="unitGuideModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Guía de Unidades de Medida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <h6 class="mb-1">Unidades</h6>
                        <p class="text-muted mb-0">Para productos contables individualmente</p>
                    </div>
                    <div class="list-group-item">
                        <h6 class="mb-1">Kilogramos (kg)</h6>
                        <p class="text-muted mb-0">Para productos que se miden por peso</p>
                    </div>
                    <div class="list-group-item">
                        <h6 class="mb-1">Litros</h6>
                        <p class="text-muted mb-0">Para productos líquidos</p>
                    </div>
                    <div class="list-group-item">
                        <h6 class="mb-1">Metros</h6>
                        <p class="text-muted mb-0">Para productos que se miden por longitud</p>
                    </div>
                    <div class="list-group-item">
                        <h6 class="mb-1">Cajas</h6>
                        <p class="text-muted mb-0">Para productos empaquetados en lotes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Guide Modal -->
<div class="modal fade" id="categoryGuideModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Guía de Categorías</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <h6 class="mb-1">Materia Prima</h6>
                        <p class="text-muted mb-0">Materiales usados en producción</p>
                    </div>
                    <div class="list-group-item">
                        <h6 class="mb-1">Productos Terminados</h6>
                        <p class="text-muted mb-0">Artículos listos para venta</p>
                    </div>
                    <div class="list-group-item">
                        <h6 class="mb-1">Suministros</h6>
                        <p class="text-muted mb-0">Materiales de uso interno</p>
                    </div>
                    <div class="list-group-item">
                        <h6 class="mb-1">Repuestos</h6>
                        <p class="text-muted mb-0">Piezas y componentes de reemplazo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showUnitGuide() {
        new bootstrap.Modal(document.getElementById('unitGuideModal')).show();
    }

    function showCategoryGuide() {
        new bootstrap.Modal(document.getElementById('categoryGuideModal')).show();
    }

    // open modals
    document.getElementById('openCategoryModal').addEventListener('click', function(){ new bootstrap.Modal(document.getElementById('categoryModal')).show(); });
    document.getElementById('openBrandModal').addEventListener('click', function(){ new bootstrap.Modal(document.getElementById('brandModal')).show(); });

    // submit via ajax category
    $('#categoryCreateSubmit').on('click', function(){
        const form = $('#categoryCreateForm');
        $.post(form.attr('action'), form.serialize())
            .done(function(res){
                $('#categoryModal').modal('hide');
                // append new option and select
                const opt = new Option(res.text, res.id, true, true);
                $('#category_id').append(opt).trigger('change');
            })
            .fail(function(xhr){
                // Clear previous
                $('#category_name').removeClass('is-invalid');
                $('#category_name_error').text('');
                $('#category_description').removeClass('is-invalid');
                $('#category_description_error').text('');

                if(xhr.status === 409 && xhr.responseJSON?.category){
                    const cat = xhr.responseJSON.category;
                    const opt = new Option(cat.name, cat.id, true, true);
                    $('#categoryModal').modal('hide');
                    $('#category_id').append(opt).trigger('change');
                } else if(xhr.status === 422){
                    const errors = xhr.responseJSON?.errors || {};
                    if(errors.name){ $('#category_name').addClass('is-invalid'); $('#category_name_error').text(errors.name[0]); }
                    if(errors.description){ $('#category_description').addClass('is-invalid'); $('#category_description_error').text(errors.description[0]); }
                } else {
                    alert('Error creando categoría: ' + (xhr.responseJSON?.message || xhr.statusText));
                }
            });
    });

    // submit via ajax brand
    $('#brandCreateSubmit').on('click', function(){
        const form = $('#brandCreateForm');
        $.post(form.attr('action'), form.serialize())
            .done(function(res){
                $('#brandModal').modal('hide');
                const opt = new Option(res.text, res.id, true, true);
                $('#brand_id').append(opt).trigger('change');
            })
            .fail(function(xhr){
                // clear
                $('#brand_name').removeClass('is-invalid');
                $('#brand_name_error').text('');
                $('#brand_description').removeClass('is-invalid');
                $('#brand_description_error').text('');

                if(xhr.status === 409 && xhr.responseJSON?.brand){
                    const br = xhr.responseJSON.brand;
                    const opt = new Option(br.name, br.id, true, true);
                    $('#brandModal').modal('hide');
                    $('#brand_id').append(opt).trigger('change');
                } else if(xhr.status === 422){
                    const errors = xhr.responseJSON?.errors || {};
                    if(errors.name){ $('#brand_name').addClass('is-invalid'); $('#brand_name_error').text(errors.name[0]); }
                    if(errors.description){ $('#brand_description').addClass('is-invalid'); $('#brand_description_error').text(errors.description[0]); }
                } else {
                    alert('Error creando marca: ' + (xhr.responseJSON?.message || xhr.statusText));
                }
            });
    });
</script>
@endpush
<!-- Category Create Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Crear Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                    <form id="categoryCreateForm" action="{{ route('categories.store') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input id="category_name" type="text" name="name" class="form-control" required />
                        <div class="invalid-feedback" id="category_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea id="category_description" name="description" class="form-control"></textarea>
                        <div class="invalid-feedback" id="category_description_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-dark" id="categoryCreateSubmit">Crear</button>
            </div>
        </div>
    </div>
</div>

<!-- Brand Create Modal -->
<div class="modal fade" id="brandModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Crear Marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                    <form id="brandCreateForm" action="{{ route('brands.store') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input id="brand_name" type="text" name="name" class="form-control" required />
                        <div class="invalid-feedback" id="brand_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea id="brand_description" name="description" class="form-control"></textarea>
                        <div class="invalid-feedback" id="brand_description_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-dark" id="brandCreateSubmit">Crear</button>
            </div>
        </div>
    </div>
</div>
@endsection