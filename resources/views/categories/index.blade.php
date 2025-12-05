@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Categorías</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categorías</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('categories.create') }}" class="btn btn-dark">
                <i class="bi bi-plus-circle me-2"></i>Nueva Categoría
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
        <thead>
                        <tr>
                            <th class="border-0 ps-4">Nombre</th>
                            <th class="border-0">Descripción</th>
                            <th class="border-0 text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $c)
                        <tr>
                            <td class="ps-4">{{ $c->name }}</td>
                            <td>{{ Str::limit($c->description, 80) }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('categories.edit', $c) }}" class="btn btn-sm btn-outline-dark me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('categories.destroy', $c) }}" method="post" style="display:inline-block">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $categories->links() }}
</div>
@endsection
