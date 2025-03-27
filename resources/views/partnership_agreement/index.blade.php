@extends('adminlte::page')

@section('title', 'Dokumen Kerjasama')

@section('content')
@include('components.alert')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-white">Daftar Dokumen</h3>
                </div>
            </div>
        
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        @canAccess('downloadPdf','partnership_agreements')
                        <a href="{{ route('partnership-agreement.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle mr-2"></i>Tambah Dokumen
                        </a>
                        @endcanAccess
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <form action="{{ route('partnership-agreement.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Cari dokumen..." id="searchInput" name="search">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 25%">Jenis Dokumen</th>
                                <th style="width: 20%" class="text-center">Nomor Surat</th>
                                <th style="width: 15%" class="text-center">Status</th>
                                <th style="width: 15%" class="text-center">Disetujui</th>
                                <th style="width: 15%" class="text-center">Pembuat</th>
                                <th style="width: 25%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="documentTable">
                            @foreach ($agreements as $agreement)
                                <tr>
                                    <td class="align-middle">{{ $agreement->type->name }}</td>
                                    <td class="align-middle text-center">{{ $agreement->number_result }}</td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-pill {{ $agreement->status_badge }}">
                                            {{ ucfirst($agreement->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if(is_null($agreement->is_approve))
                                            <span class="badge badge-warning">Belum</span>
                                        @else
                                            <span class="badge {{ $agreement->is_approve ? 'badge-success' : 'badge-danger' }}">
                                                {{ $agreement->is_approve ? 'Ya' : 'Tidak' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $agreement->updateCreate->name }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center d-flex">
                                        @canAccess('downloadPdf','partnership_agreements')
                                        <a href="{{ route('partnership-agreement.downloadPdf', $agreement) }}" class="btn btn-sm btn-info mr-1" title="Preview PDF"><i class="fas fa-file-pdf"></i></a>
                                        @endcanAccess
                                        @canAccess('edit','partnership_agreements')
                                        <a href="{{ route('partnership-agreement.edit', $agreement) }}" class="btn btn-sm btn-warning mr-1" title="Edit"><i class="fas fa-edit"></i></a>
                                        @endcanAccess
                                        @canAccess('destroy','partnership_agreements')
                                        @if($agreement->isPermission('delete'))
                                        <form action="{{ route('partnership-agreement.destroy', $agreement) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        @endif
                                        @endcanAccess
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $agreements->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        border-radius: 0.75rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .table thead th {
        border-bottom: 2px solid #e9ecef;
        vertical-align: middle;
    }
    
    .badge-pill {
        padding: 0.5em 1em;
        min-width: 80px;
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .input-group-text {
        background-color: #fff;
        border-left: 0;
    }
    
    #searchInput:focus + .input-group-append .input-group-text {
        border-color: #80bdff;
    }
</style>
@stop