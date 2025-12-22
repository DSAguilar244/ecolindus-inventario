@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Editar Categoría</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}" class="text-decoration-none">Categorías</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-list-ul me-2"></i>Editar Categoría</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('categories.update', $category) }}" method="post" id="categoryEditForm">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="category-name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required maxlength="255" autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Nombre visible de la categoría.</div>
                        </div>

                        <div class="mb-3">
                            {{-- slug removed; field not used anymore --}}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea id="category-description" name="description" class="form-control @error('description') is-invalid @enderror" maxlength="2000">{{ old('description', $category->description) }}</textarea>
                            <div class="form-text"><span id="category-desc-count">{{ strlen(old('description', $category->description ?? '')) }}</span>/2000</div>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('categories.index') }}" class="btn btn-light">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" form="categoryEditForm" class="btn btn-dark">
                            <i class="bi bi-check-circle me-2"></i>Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Información</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">Edita los datos de la categoría. Usa nombres claros para facilitar la búsqueda de productos.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    (function(){
        const name = document.getElementById('category-name');
        const desc = document.getElementById('category-description');
        const descCount = document.getElementById('category-desc-count');

        function slugify(v){
            return v.toString().toLowerCase()
                .normalize('NFD').replace(/\p{Diacritic}/gu, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim().replace(/\s+/g, '-')
                .replace(/-+/g,'-');
        }

        // slug generation removed

        if(desc && descCount){ desc.addEventListener('input', function(){ descCount.textContent = desc.value.length; }); }
    })();
</script>
@endpush

@endsection
