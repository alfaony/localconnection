@extends('adminlte::page')

@section('title', 'Surat Langganan')
@section('content_header')
    <h1>Daftar Surat Langganan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>List Surat Langganan</span>
        <div class="ml-auto">
            <a href="{{ route('subscribe-letter.create') }}" class="btn btn-sm btn-primary">+ Tambah Surat</a>
        </div>
    </div>

    <div class="card-body">
        @include('components.alert')

        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('subscribe-letter.index') }}" method="get" class="mt-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="{{ request()->query('search') }}" placeholder="Cari...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama Surat</th>
                        <th>Berlaku</th>
                        <th>PIC</th>
                        <th>Dokumen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($letters as $letter)
                    <tr>
                        <td>{{ $letter->name }}</td>
                        <td>
                            {{ $letter->valid_from }} – 
                            <span class="badge bg-{{ $letter->getColorStatusFor('valid_until') }}">
                                {{ $letter->valid_until }}
                            </span>
                        </td>
                        <td>{{ $letter->responsibleUser->name ?? '-' }}</td>
                        <td>
                            @if($letter->document_path)
                                <a href="{{ s3_asset(true,10, $letter->document_path) }}" target="_blank">📄</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('subscribe-letter.show', $letter->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('subscribe-letter.edit', $letter->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('subscribe-letter.destroy', $letter->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Belum ada surat.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $letters->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@stop