<x-guest-layout>
    @if (session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
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
                    <i class="bi bi-key"></i>
                </span>
                <input id="password" 
                       type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                           placeholder="••••••••">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
            </div>
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
            </div>

            @push('scripts')
            <script>
                document.getElementById('togglePassword').addEventListener('click', function() {
                    const passwordInput = document.getElementById('password');
                    const icon = this.querySelector('i');
                
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                });
            </script>
            @endpush

        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Recordar sesión
                </label>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-dark">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Iniciar sesión
            </button>
        </div>

        @if(Route::has('register'))
            <div class="d-grid gap-2 mt-3">
                <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-person-plus me-2"></i>
                    Registrarse
                </a>
            </div>
        @endif

        @if (Route::has('password.request'))
            <div class="auth-footer">
                <a class="auth-link" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
