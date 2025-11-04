@extends('layouts.app')

@section('content')
<h2 class="mb-4">➕ Nuevo proveedor</h2>

<form action="{{ route('suppliers.store') }}" method="POST" class="bg-dark text-white p-4 rounded shadow-sm">
    @csrf

    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Nombre del proveedor</label>
            <input type="text" name="name" id="name" class="form-control bg-light" required>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="col-md-6">
            <label for="contact" class="form-label">Contacto</label>
            <input type="text" name="contact" id="contact" class="form-control bg-light">
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" name="email" id="email" class="form-control bg-light">
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success px-4">💾 Guardar</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-light px-4">↩️ Cancelar</a>
    </div>
</form>
@endsection