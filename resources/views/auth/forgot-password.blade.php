<x-guest-layout>
    <div class="text-center mb-4">
        <i class="bi bi-question-circle-fill display-5 text-muted"></i>
        <h5 class="mt-3 mb-3">¿Olvidaste tu contraseña?</h5>
        <p class="text-muted">
            No te preocupes. Simplemente ingresa tu correo electrónico y te enviaremos un enlace para que puedas crear una nueva contraseña.
        </p>
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4">
            Se ha enviado el enlace de recuperación a tu correo electrónico.
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

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
                       autofocus
                       placeholder="nombre@empresa.com">
            </div>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-dark">
                <i class="bi bi-envelope-paper me-2"></i>
                Enviar enlace de recuperación
            </button>
        </div>

        <div class="auth-footer">
            <a href="{{ route('login') }}" class="auth-link">
                <i class="bi bi-arrow-left me-1"></i>
                Volver al inicio de sesión
            </a>
        </div>
    </form>
</x-guest-layout>
