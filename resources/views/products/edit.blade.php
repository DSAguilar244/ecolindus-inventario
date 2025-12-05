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
                        <input id="category_name" type="text" name="name" aria-describedby="category_name_error" aria-required="true" class="form-control" required />
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
                        <input id="brand_name" type="text" name="name" aria-describedby="brand_name_error" aria-required="true" class="form-control" required />
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
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Editar Producto</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Productos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Edit Form Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Información del Producto
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.update', $product) }}" method="POST" id="editForm">
                        @csrf @method('PUT')

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nombre del producto</label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $product->name) }}" required>
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
                                                <option value="{{ $cat->id }}" {{ (old('category_id') ?? $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                                                <option value="{{ $b->id }}" {{ (old('brand_id') ?? $product->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="openBrandModal" title="Crear marca">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="unit" class="form-label">Unidad de medida</label>
                                    <input type="text" name="unit" id="unit" 
                                           class="form-control @error('unit') is-invalid @enderror" 
                                           value="{{ old('unit', $product->unit) }}" required>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="code" class="form-label">Código</label>
                                    <input type="text" name="code" id="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code', $product->code) }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price" class="form-label">Precio</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="price" id="price" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               value="{{ old('price', $product->price ?? 0) }}" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="stock" class="form-label">Stock actual</label>
                                    <div class="input-group">
                                        <input type="number" name="stock" id="stock" 
                                               class="form-control @error('stock') is-invalid @enderror" 
                                               value="{{ old('stock', $product->stock) }}" min="0">
                                        <span class="input-group-text">{{ $product->unit }}</span>
                                    </div>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="min_stock" class="form-label">Stock mínimo</label>
                                    <div class="input-group">
                                        <input type="number" name="min_stock" id="min_stock" 
                                               class="form-control @error('min_stock') is-invalid @enderror" 
                                               value="{{ old('min_stock', $product->min_stock) }}" min="0">
                                        <span class="input-group-text">{{ $product->unit }}</span>
                                    </div>
                                    @error('min_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tax" class="form-label">Impuesto</label>
                                    <select name="tax" id="tax" class="form-select">
                                        <option value="0" {{ old('tax', $product->tax) == 0 ? 'selected' : '' }}>0%</option>
                                        <option value="15" {{ old('tax', $product->tax) == 15 ? 'selected' : '' }}>15%</option>
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
                        <button type="submit" form="editForm" class="btn btn-dark">
                            <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Product Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Estado del Producto
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            @if($product->stock < $product->min_stock)
                                <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                            @elseif($product->stock >= $product->min_stock * 2)
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            @else
                                <i class="bi bi-exclamation-circle-fill text-warning fs-4"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Estado de Stock</h6>
                            <p class="mb-0 text-muted">
                                @if($product->stock < $product->min_stock)
                                    Stock bajo - Requiere atención
                                @elseif($product->stock >= $product->min_stock * 2)
                                    Stock óptimo
                                @else
                                    Stock moderado
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 10px;">
                        @php
                            $stockPercentage = $product->min_stock > 0 ? ($product->stock / ($product->min_stock * 2)) * 100 : 0;
                            $progressClass = $stockPercentage < 50 ? 'bg-danger' : 
                                           ($stockPercentage < 100 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="progress-bar {{ $progressClass }}" 
                             role="progressbar" 
                             style="width: {{ min($stockPercentage, 100) }}%">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Stock Mínimo</span>
                        <span class="fw-bold">{{ $product->min_stock }} {{ $product->unit }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Stock Actual</span>
                        <span class="fw-bold">{{ $product->stock }} {{ $product->unit }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('movements.create', ['product' => $product->id, 'type' => 'in']) }}" 
                           class="btn btn-outline-success">
                            <i class="bi bi-plus-circle me-2"></i>Registrar Entrada
                        </a>
                        <a href="{{ route('movements.create', ['product' => $product->id, 'type' => 'out']) }}" 
                           class="btn btn-outline-danger">
                            <i class="bi bi-dash-circle me-2"></i>Registrar Salida
                        </a>
                        <button type="button" class="btn btn-outline-dark"
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal"
                                data-product-id="{{ $product->id }}"
                                data-product-name="{{ $product->name }}">
                            <i class="bi bi-trash me-2"></i>Eliminar Producto
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Eliminar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                    <h5 class="mb-3">¿Está seguro que desea eliminar este producto?</h5>
                    <p class="text-muted mb-0">Esta acción eliminará permanentemente el producto:</p>
                    <p class="fw-bold mb-0" id="productNameToDelete">{{ $product->name }}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Producto
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('openCategoryModal').addEventListener('click', function(){ new bootstrap.Modal(document.getElementById('categoryModal')).show(); });
    document.getElementById('openBrandModal').addEventListener('click', function(){ new bootstrap.Modal(document.getElementById('brandModal')).show(); });

    // submit via ajax category
    $('#categoryCreateSubmit').on('click', function(){
        const form = $('#categoryCreateForm');
        $.post(form.attr('action'), form.serialize())
            .done(function(res){
                $('#categoryModal').modal('hide');
                const opt = new Option(res.text, res.id, true, true);
                $('#category_id').append(opt).trigger('change');
            })
            .fail(function(xhr){
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