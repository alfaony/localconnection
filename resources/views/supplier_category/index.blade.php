@extends('adminlte::page')

@section('title', 'Supplier Categories')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Kategori Supplier</h5>
                <div class="ml-auto">
                    @canAccess('store','supplier_categories')
                    <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">
                        <i class="fa fa-plus"></i> Kategori
                    </button>
                    @endcanAccess
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td class="text-right">
                                @canAccess('edit','supplier_categories')
                                <a href="{{ route('supplier-category.edit', $category->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i>
                                </a>
                                @endcanAccess

                                @canAccess('destroy','supplier_categories')
                                <form action="{{ route('supplier-category.destroy', $category->id) }}" method="POST" class="d-inline" 
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> </button>
                                </form>
                                @endcanAccess
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $categories->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('supplier-category.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Kategori Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="name">Nama Kategori</label>
                    <input type="text" name="name" class="form-control" required placeholder="Masukkan kategori supplier">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
@if(request()->has('edit'))
@php
    $editingCategory = \App\Models\SupplierCategory::byCompany(Auth::user()->company_id)->find(request('edit'));
@endphp
@if($editingCategory)
<div class="modal fade show d-block" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog">
        <form action="{{ route('supplier-category.update', $editingCategory->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Kategori Supplier</h5>
                    <a href="{{ route('supplier-category.index') }}" class="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </div>
                <div class="modal-body">
                    <label for="name">Nama Kategori</label>
                    <input type="text" name="name" class="form-control" required value="{{ $editingCategory->name }}">
                </div>
                <div class="modal-footer">
                    <a href="{{ route('supplier-category.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endif

@endsection