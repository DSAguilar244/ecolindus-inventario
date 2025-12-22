@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1">Perfil de usuario</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Perfil</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-4">
        {{-- Información del perfil --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex align-items-center py-3">
                    <i class="bi bi-person-vcard me-2 text-primary"></i>
                    <span class="fw-semibold">Información del perfil</span>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        {{-- Actualizar contraseña --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex align-items-center py-3">
                    <i class="bi bi-shield-lock me-2 text-primary"></i>
                    <span class="fw-semibold">Cambiar contraseña</span>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        {{-- Eliminar cuenta --}}
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex align-items-center py-3">
                    <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                    <span class="fw-semibold">Zona de peligro</span>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection