@extends('adminlte::page')

@section('title', 'Alur Kerja')

@section('content_header')
    <h1>Daftar Alur Kerja</h1>
@endsection

@section('content')
@include('components.alert')
    <div class="d-flex justify-content-between align-items-center">
        <div class="mb-3">
            @canAccess('create','flowcharts')
            <a href="{{ route('flowchart.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Alur Kerja
            </a>
            @endcanAccess
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <form action="{{ request()->url() }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" value="{{ request()->query('search') }}" placeholder="Cari alur kerja">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($charts as $chart)
                            <tr>
                                <td>{{ $chart->name }}</td>
                                <td>{{ $chart->created_at->format('d M Y') }}</td>
                                <td>
                                    @canAccess('show','flowcharts')
                                    <a href="{{ route('flowchart.show', $chart->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcanAccess

                                    @canAccess('edit','flowcharts')
                                    <a href="{{ route('flowchart.edit', $chart->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcanAccess
                                    
                                    @canAccess('destroy','flowcharts')
                                    <form action="{{ route('flowchart.destroy', $chart->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus Alur Kerja ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanAccess
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada Alur Kerja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-2">
                    {{ $charts->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection