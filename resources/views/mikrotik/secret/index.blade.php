@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar PPP Secrets</h2>
    <a href="{{ route('mikrotik-secrets.create') }}" class="btn btn-primary mb-3">Tambah Secret</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Service</th>
                <th>Profile</th>
                <th>Remote Address</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($secrets as $secret)
            <tr>
                <td>{{ $secret['name'] ?? '-' }}</td>
                <td>{{ $secret['service'] ?? '-' }}</td>
                <td>{{ $secret['profile'] ?? '-' }}</td>
                <td>{{ $secret['remote-address'] ?? '-' }}</td>
                <td>
                    <form action="{{ route('mikrotik-secrets.disconnect', $secret['name']) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">Disconnect</button>
                    </form>
                    <a href="{{ route('mikrotik-secrets.edit', $secret['.id']) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('mikrotik-secrets.destroy', $secret['.id']) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Yakin hapus secret ini?')" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Belum ada secret.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection