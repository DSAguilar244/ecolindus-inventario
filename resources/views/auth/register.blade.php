<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-4">
            <label for="name" class="form-label">Nombre completo</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-person"></i>
                </span>
                <input id="name" 
                       type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus 
                       autocomplete="name"
                       placeholder="Juan Pérez">
            </div>
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="form-label">Correo electrónico</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                </span>
                <input id="email" 
                       type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autocomplete="username"
                       placeholder="nombre@empresa.com">
            </div>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-shield-lock"></i>
                </span>
                <input id="password" 
                       type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       name="password" 
                       required 
                       autocomplete="new-password"
                       placeholder="••••••••">
            </div>
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-shield-check"></i>
                </span>
                <input id="password_confirmation" 
                       type="password" 
                       class="form-control" 
                       name="password_confirmation" 
                       required 
                       autocomplete="new-password"
                       placeholder="••••••••">
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-dark">
                <i class="bi bi-person-plus me-2"></i>
                Crear cuenta
            </button>
        </div>

        <div class="auth-footer">
            <a class="auth-link" href="{{ route('login') }}">
                ¿Ya tienes una cuenta? Inicia sesión
            </a>
        </div>
    </form>
</x-guest-layout>
