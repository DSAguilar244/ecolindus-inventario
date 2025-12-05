<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ECOLINDUS') }}</title>

        {{-- Bootstrap CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        {{-- Bootstrap Icons --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            body {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                min-height: 100vh;
            }
            .auth-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                padding: 2rem;
            }
            .auth-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 0 40px rgba(0,0,0,0.1);
                overflow: hidden;
                width: 100%;
                max-width: 450px;
            }
            .auth-header {
                background: #212529;
                padding: 2rem;
                text-align: center;
                color: white;
            }
            .auth-logo {
                font-size: 2rem;
                font-weight: bold;
                margin-bottom: 0.5rem;
                letter-spacing: 1px;
            }
            .auth-subtitle {
                font-size: 1rem;
                opacity: 0.8;
            }
            .auth-body {
                padding: 2rem;
            }
            .form-control:focus {
                border-color: #212529;
                box-shadow: 0 0 0 0.25rem rgba(33, 37, 41, 0.15);
            }
            .btn-dark {
                background-color: #212529;
                border-color: #212529;
                padding: 0.6rem 2rem;
            }
            .btn-dark:hover {
                background-color: #343a40;
                border-color: #343a40;
            }
            .auth-footer {
                text-align: center;
                padding-top: 1rem;
                border-top: 1px solid #dee2e6;
                margin-top: 2rem;
            }
            .auth-link {
                color: #212529;
                text-decoration: none;
            }
            .auth-link:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="auth-wrapper">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">
                        <i class="bi bi-box-seam"></i>
                        ECOLINDUS
                    </div>
                    <div class="auth-subtitle">Sistema de Inventario</div>
                </div>

                <div class="auth-body">
                    {{ $slot }}
                </div>
            </div>
        </div>

        {{-- Bootstrap JS --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
            @stack('scripts')
    </body>
</html>
