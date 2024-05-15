@extends('adminlte::page')

@section('content')
<div class="container p-3 mt-3">
    <h2>Daftar Hak Cipta</h2>
    @canAccess('create','ip_rights')
    <a href="{{ route('ip-right.create') }}" class="btn btn-primary mb-4"><i class="fa fa-plus"></i> Tambah Hak Cipta</a>
    @endcanAccess
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @canAccess('index','task_assigns')
    <form method="GET" action="{{ route('ip-right.index') }}" class="mb-3">
        <div class="row g-1 align-items-end">
            <div class="col-12 col-md-2">
                <select class="form-control" id="task" name="status">
                    <option selected disabled>-- Status --</option>
                    @foreach($status as $key => $values)
                    <option value="{{ $key }}">{{ $values }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-auto mt-2">
                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
            </div>
        </div>
    </form>
    @endcanAccess
    <div class="table-responsive-md">
            <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-2">Tanggal Terbit</th>
                    <th>Nama Ciptaan</th>
                    <th>No. Patent / Hak Cipta</th>
                    <th class="col-1">Poin</th>
                    <th>Status</th>
                    <th>Pengajuan</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ipRights as $ipRight)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($ipRight->patent_date)->format('d-m-Y') }}</td>
                    <td>{{ $ipRight->name }}</td>
                    <td>{{ $ipRight->patent_number }}</td>
                    <td> {{ $ipRight->point ?? "-" }} </td>
                    <td>
                        @switch($ipRight->status)
                            @case('in review')
                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                @break
                            @case('complete')
                                <i class="fa fa-check" style="color: green;"></i> Complete
                                @break
                        @endswitch
                    </td>
                    <td>
                        {{ $ipRight->user->name ?? '-' }}
                    </td>
                    <td>
                        @canAccess('show','ip_rights')
                        <a href="{{ route('ip-right.show', $ipRight->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                        @endcanAccess
                        @if(!$ipRight->approved)
                        @canAccess('edit','ip_rights')
                        <a href="{{ route('ip-right.edit', $ipRight->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                        @canAccess('destroy','ip_rights')
                        <form action="{{ route('ip-right.destroy', $ipRight->slug) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                        </form>
                        @endcanAccess
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center">
        {{ $ipRights->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>
</div>
@endsection
@section('css')
    <style>
        body
        {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
        }
    </style>
@stop
