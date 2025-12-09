<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ECOLINDUS Inventario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    {{-- jQuery + Select2 for enhanced selects --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Estilos personalizados --}}
    <style>
        body {
            background-color: #f8f9fa;
        }

        /* Navbar styles */
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }

        .navbar .nav-link {
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .navbar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
        }

        .navbar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 0.375rem;
        }

        /* Card styles */
        .card {
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .card-text {
            font-size: 1.2rem;
        }

        /* Table styles */
        .table th, .table td {
            vertical-align: middle;
        }

        /* Pagination styles */
        .pagination {
            justify-content: center;
            margin-top: 1rem;
        }

        .pagination .page-link {
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
            border-radius: 0.25rem;
        }

        /* Dropdown styles */
        .dropdown-menu {
            border: 0;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .dropdown-menu-dark .dropdown-item {
            padding: 0.5rem 1rem;
            transition: all 0.2s ease-in-out;
        }

        .dropdown-menu-dark .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Breadcrumb styles */
        .breadcrumb-item + .breadcrumb-item::before {
            content: "•";
        }

        .breadcrumb-item a {
            color: #0d6efd;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            text-decoration: underline;
        }

        /* Form styles */
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="bi bi-box-seam me-2"></i>
            ECOLINDUS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('products.index') }}">
                        <i class="bi bi-box me-1"></i> Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('brands.index') }}">
                        <i class="bi bi-tags me-1"></i> Marcas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('categories.index') }}">
                        <i class="bi bi-list-ul me-1"></i> Categorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('customers.index') }}">
                        <i class="bi bi-people me-1"></i> Clientes
                    </a>
                </li>
                @can('manage-users')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-shield-lock me-1"></i> Usuarios
                    </a>
                </li>
                @endcan
                {{-- Proveedores desactivados --}}
                {{-- <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('suppliers.index') }}">
                        <i class="bi bi-truck me-1"></i> Proveedores
                    </a>
                </li> --}}
                {{-- Movimientos desactivados --}}
                {{-- <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('movements.index') }}">
                        <i class="bi bi-arrow-left-right me-1"></i> Movimientos
                    </a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="{{ route('invoices.index') }}">
                        <i class="bi bi-receipt-cutoff me-1"></i> Ventas
                    </a>
                </li>
            </ul>

            @auth
            <div class="dropdown text-end">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-2"></i>
                    <span>{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark text-small shadow" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.edit') }}">
                            <i class="bi bi-gear me-2"></i> Editar perfil
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item d-flex align-items-center text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            @endauth
        </div>
    </nav>

    <div class="container mt-4">
        {{-- Flash messages and validation errors (success shown as toast to avoid layout shift) --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function(){ showGlobalToast(@json(session('success')), { classname: 'bg-success text-white', delay: 2000 }); });
            </script>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Global toast container for smooth non-blocking alerts --}}
    <div id="globalToastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="globalToastTemplate" class="toast align-items-center bg-dark text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000" style="display:none">
            <div class="d-flex">
                <div class="toast-body">Acción realizada</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        function showGlobalToast(message, opts = { delay: 3000, classname: 'bg-dark text-white' }){
            const container = document.getElementById('globalToastContainer');
            if(!container) return;
            const template = document.getElementById('globalToastTemplate');
            const el = template.cloneNode(true);
            el.style.display = 'block';
            el.removeAttribute('id');
            el.className = 'toast align-items-center ' + (opts.classname || 'bg-dark text-white') + ' border-0';
            el.querySelector('.toast-body').textContent = message;
            container.appendChild(el);
            const bs = new bootstrap.Toast(el, { delay: opts.delay });
            bs.show();
            el.addEventListener('hidden.bs.toast', function(){ el.remove(); });
        }
    </script>

    {{-- Scripts dinámicos desde las vistas --}}
    @stack('scripts')

    {{-- Global toast container for smooth non-blocking alerts --}}
    <div id="globalToastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="globalToastTemplate" class="toast align-items-center bg-dark text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000" style="display:none">
            <div class="d-flex">
                <div class="toast-body">Acción realizada</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        function showGlobalToast(message, opts = { delay: 3000, classname: 'bg-dark text-white' }){
            const container = document.getElementById('globalToastContainer');
            if(!container) return;
            const template = document.getElementById('globalToastTemplate');
            const el = template.cloneNode(true);
            el.style.display = 'block';
            el.removeAttribute('id');
            el.className = 'toast align-items-center ' + (opts.classname || 'bg-dark text-white') + ' border-0';
            el.querySelector('.toast-body').textContent = message;
            container.appendChild(el);
            const bs = new bootstrap.Toast(el, { delay: opts.delay });
            bs.show();
            el.addEventListener('hidden.bs.toast', function(){ el.remove(); });
        }
    </script>
</body>
</html>