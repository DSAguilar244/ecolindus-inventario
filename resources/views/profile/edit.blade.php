@extends('layouts.app')

@section('content')
<h2 class="mb-4">⚙️ Perfil de usuario</h2>

<div class="row g-4">
    {{-- Información del perfil --}}
    <div class="col-md-6">
        <div class="card bg-dark text-white shadow-sm">
            <div class="card-header fw-semibold">🧾 Información del perfil</div>
            <div class="card-body bg-light text-dark">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>

    {{-- Actualizar contraseña --}}
    <div class="col-md-6">
        <div class="card bg-dark text-white shadow-sm">
            <div class="card-header fw-semibold">🔒 Cambiar contraseña</div>
            <div class="card-body bg-light text-dark">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    {{-- Eliminar cuenta --}}
    <div class="col-md-12">
        <div class="card bg-dark text-white shadow-sm">
            <div class="card-header fw-semibold">⚠️ Eliminar cuenta</div>
            <div class="card-body bg-light text-dark">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection