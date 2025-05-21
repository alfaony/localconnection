@extends('adminlte::page')

@section('title', 'Permintaan Barang')

@section('content_header')
    <h1>Permintaan Barang</h1>
@stop

@section('content')

    @include('components.alert')

    <a href="{{ route('item-request.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Buat Permintaan
    </a>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Estimasi Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $req->item_name }}</td>
                            <td>{{ $req->category->name ?? '-' }}</td>
                            <td>Rp{{ number_format($req->estimated_price) }}</td>
                            <td><span class="badge bg-info">{{ $req->status }}</span></td>
                            <td>
                                <a href="{{ route('item-request.edit', $req) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('item-request.destroy', $req) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus request ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada permintaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $requests->links() }}
    </div>
@stop