@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Gestión de Productos</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Productos</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.create') }}" class="btn btn-dark">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Producto
            </a>
            <a href="{{ route('products.export.pdf') }}" class="btn btn-outline-dark">
                <i class="bi bi-file-pdf me-2"></i>Exportar PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Products List Card -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4">Producto</th>
                            <th class="border-0">Categoría</th>
                            <th class="border-0">Marca</th>
                            <th class="border-0">Unidad</th>
                            <th class="border-0">Stock Actual</th>
                            <th class="border-0">Stock Mínimo</th>
                            <th class="border-0">Precio</th>
                            <th class="border-0 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $product->name }} <small class="text-muted">{{ $product->code }}</small></h6>
                                        @if($product->stock < $product->min_stock)
                                            <span class="badge bg-danger">Stock Bajo</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($product->categoryModel)
                                    <span class="badge bg-dark text-white">{{ $product->categoryModel->name }}</span>
                                @elseif(!empty($product->category))
                                    <span class="text-muted">{{ $product->category }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($product->brand)
                                    <span class="badge bg-light text-dark">{{ $product->brand->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $product->unit }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->stock < $product->min_stock)
                                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                    @elseif($product->stock >= $product->min_stock * 2)
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    @else
                                        <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>
                                    @endif
                                    <span class="fw-semibold {{ $product->stock < $product->min_stock ? 'text-danger' : '' }}">
                                        {{ $product->stock }}
                                    </span>
                                </div>
                            </td>
                            <td>{{ $product->min_stock }}</td>
                            <td>{{ number_format($product->price,2) }}
                                <div><small class="text-muted">PVP: {{ number_format($product->price * (1 + ($product->tax ?? 0)/100),2) }} | {{ $product->tax }}%</small></div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-dark me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">No hay productos registrados.</p>
                                <a href="{{ route('products.create') }}" class="btn btn-dark mt-3">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar Producto
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($products->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <p class="text-muted mb-0">
            Mostrando {{ $products->firstItem() }} a {{ $products->lastItem() }} de {{ $products->total() }} productos
        </p>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                <li class="page-item {{ $products->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $products->previousPageUrl() }}" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                {{-- Numbered Page Links --}}
                @foreach ($products->links()->elements[0] as $page => $url)
                    @if ($page == $products->currentPage())
                        <li class="page-item active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                <li class="page-item {{ !$products->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $products->nextPageUrl() }}" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    @endif

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Eliminar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                    <h5 class="mb-3">¿Está seguro que desea eliminar este producto?</h5>
                    <p class="text-muted mb-0">Esta acción eliminará permanentemente el producto:</p>
                    <p class="fw-bold mb-0" id="productNameToDelete"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" action="" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Producto
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const productId = button.getAttribute('data-product-id');
                const productName = button.getAttribute('data-product-name');
                
                const form = this.querySelector('#deleteForm');
                const productNameElement = this.querySelector('#productNameToDelete');
                
                form.action = `/products/${productId}`;
                productNameElement.textContent = productName;
            });
        });
    </script>
    @endpush
@endsection