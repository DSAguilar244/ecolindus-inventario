@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Editar Marca</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('brands.index') }}" class="text-decoration-none">Marcas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-tags me-2"></i>Editar Marca</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('brands.update', $brand) }}" method="post" id="brandEditForm">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="brand-name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $brand->name) }}" required maxlength="255" autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Nombre público de la marca.</div>
                        </div>

                        <div class="mb-3">
                            {{-- slug removed, field deprecated --}}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea id="brand-description" name="description" class="form-control @error('description') is-invalid @enderror" maxlength="2000">{{ old('description', $brand->description) }}</textarea>
                            <div class="form-text"><span id="brand-desc-count">{{ strlen(old('description', $brand->description ?? '')) }}</span>/2000</div>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('brands.index') }}" class="btn btn-light">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" form="brandEditForm" class="btn btn-dark">
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
                    <p class="text-muted mb-2">Edita los datos de la marca. El nombre debe ser único para evitar duplicados.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    (function(){
        const name = document.getElementById('brand-name');
        const desc = document.getElementById('brand-description');
        const descCount = document.getElementById('brand-desc-count');

        function slugify(v){
            return v.toString().toLowerCase()
                .normalize('NFD').replace(/\p{Diacritic}/gu, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim().replace(/\s+/g, '-')
                .replace(/-+/g,'-');
        }

        // slug logic removed; slug field no longer present

        if(desc && descCount){ desc.addEventListener('input', function(){ descCount.textContent = desc.value.length; }); }
    })();
</script>
@endpush

@endsection
