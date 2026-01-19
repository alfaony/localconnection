@extends('adminlte::page')

@section('title', 'Tipe Parameter')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-cog"></i> Master Tipe Parameter</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Tipe Parameter</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="row mb-3">
        @canAccess('create','partner_parameter_types')
        <div class="col-12">
            <a href="{{ route('partner-parameter-type.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Tipe Parameter Baru
            </a>
        </div>
        @endcanAccess
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Tipe Parameter</h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ $parameters->total() }} Total</span>
            </div>
        </div>
        <div class="card-body">
            @if($parameters->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Nama</th>
                                <th>Kode</th>
                                <th>Unit</th>
                                <th>Deskripsi</th>
                                <th width="80">Status</th>
                                <th width="80">Urutan</th>
                                <th width="100">Digunakan Di</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parameters as $parameter)
                                <tr>
                                    <td>{{ $loop->iteration + ($parameters->currentPage() - 1) * $parameters->perPage() }}</td>
                                    <td><strong>{{ $parameter->name }}</strong></td>
                                    <td><code>{{ $parameter->code }}</code></td>
                                    <td>
                                        @if($parameter->unit)
                                            <span class="badge badge-info">{{ $parameter->unit }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $parameter->description ?: '-' }}</small>
                                    </td>
                                    <td>
                                        @if($parameter->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light">{{ $parameter->sort_order }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $usageCount = $parameter->targetValues()->count();
                                        @endphp
                                        @if($usageCount > 0)
                                            <span class="badge badge-warning" title="Digunakan di {{ $usageCount }} target">
                                                {{ $usageCount }} <i class="fas fa-link"></i>
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @canAccess('edit','partner_parameter_types')
                                            <a href="{{ route('partner-parameter-type.edit', $parameter) }}" 
                                               class="btn btn-sm btn-warning mr-1 mb-1" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcanAccess
                                            
                                            @canAccess('toggleActive','partner_parameter_types')
                                            <form action="{{ route('partner-parameter-type.toggle-active', $parameter) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $parameter->is_active ? 'btn-secondary' : 'btn-success' }} mr-1 mb-1" 
                                                        title="{{ $parameter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="fas fa-{{ $parameter->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                                </button>
                                            </form>
                                            @endcanAccess
                                            
                                            @canAccess('destroy','partner_parameter_types')
                                            <form action="{{ route('partner-parameter-type.destroy', $parameter) }}" 
                                                  method="POST" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Apakah Anda yakin? Ini akan menghapus tipe parameter secara permanen.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger mr-1 mb-1" 
                                                        title="Hapus"
                                                        {{ $usageCount > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcanAccess
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $parameters->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada tipe parameter yang ditemukan.</p>
                    <a href="{{ route('partner-parameter-type.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Tipe Parameter Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Info Card -->
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Tentang Tipe Parameter</h3>
        </div>
        <div class="card-body">
            <p class="mb-2">
                <strong>Tipe parameter</strong> digunakan untuk menentukan metrik apa yang perlu dicapai oleh mitra. 
                Contoh: Pendapatan, Kesepakatan, Sertifikasi, Jumlah Pelatihan, Nilai Pipeline.
            </p>
            <p class="mb-0">
                <i class="fas fa-exclamation-triangle text-warning"></i> 
                <strong>Catatan:</strong> Tipe parameter yang sudah digunakan dalam target mitra tidak dapat dihapus.
            </p>
        </div>
    </div>
@stop