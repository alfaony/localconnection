{{-- index.blade.php --}}
@extends('adminlte::page')

@section('title', 'PPP Profiles')

@section('content_header')
    <h1>PPP Profiles Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <a href="{{ route('mikrotik-profile.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Create
            </a>
        </div>
    </div>
    
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('error') }}
            </div>
        @endif

        <form class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="q" value="{{ $search }}" placeholder="Cari nama/rate-limit/pool">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Filter</button>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <select name="per_page" class="form-control" onchange="this.form.submit()">
                    @foreach([10,25,50,100] as $n)
                        <option value="{{ $n }}" @selected($perPage==$n)>{{ $n }}/page</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Name</th>
                        <th>Rate Limit</th>
                        <th>Remote Address</th>
                        <th>Local Address</th>
                        <th>Only One</th>
                        <th>Comment</th>
                        <th style="width:150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $p)
                        <tr>
                            <td>{{ $p['name'] ?? '-' }}</td>
                            <td>{{ $p['rate-limit'] ?? '-' }}</td>
                            <td>{{ $p['remote-address'] ?? '-' }}</td>
                            <td>{{ $p['local-address'] ?? '-' }}</td>
                            <td>{{ $p['only-one'] ?? '-' }}</td>
                            <td>{{ $p['comment'] ?? '' }}</td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-primary" href="{{ route('mikrotik-profile.edit', $p['name']) }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form class="d-inline" method="post" action="{{ route('mikrotik-profile.destroy', $p['name']) }}" onsubmit="return confirm('Hapus profile ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card-footer clearfix">
        {{ $profiles->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>
@stop