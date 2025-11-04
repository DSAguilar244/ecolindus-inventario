<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ECOLINDUS Inventario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Estilos personalizados --}}
    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }

        .pagination {
            justify-content: center;
            margin-top: 1rem;
        }

        .pagination .page-link {
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
            border-radius: 0.25rem;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .card-text {
            font-size: 1.2rem;
        }

        .dropdown-menu-dark .dropdown-item:hover {
            background-color: #343a40;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
        <a class="navbar-brand" href="{{ route('dashboard') }}">ECOLINDUS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('suppliers.index') }}">Proveedores</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('movements.index') }}">Movimientos</a></li>
            </ul>

            @auth
            <div class="dropdown text-end">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2">👤 {{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark text-small shadow" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">⚙️ Editar perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger">🚪 Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
            @endauth
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Scripts dinámicos desde las vistas --}}
    @stack('scripts')
</body>
</html>