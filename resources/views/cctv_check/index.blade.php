@extends('adminlte::page')
@section('content_header')
    <h2>Kontrol Cctv</h2>
@stop
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Kontrol Cctv Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Kontrol Cctv Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Kontrol Cctv Berhasil Terhapus</div>
    @endif

</div>

<div class="container p-3">
    <div class="card">
        <div class="card-body">
            @canAccess('create','cctv_checks')
            <a href="{{ route('cctv-check.create') }}" class="btn btn-primary mb-3"> Kontrol Cctv</a>
            @endcanAccess
            @canAccess('index','cctv_checks')
            <form method="GET" action="{{ route('cctv-check.index') }}" class="mb-3">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <select class="form-control" id="task" name="task">
                            @if(!request('task'))
                            <option value="today" selected>Hari Ini</option>
                            <option value="all" {{ request('task') == "all" ? 'selected' : '' }}>Semua</option>
                            @else
                            <option value="all" {{ request('task') == "all" ? 'selected' : '' }}>Semua</option>
                            <option value="today" {{ request('task') == "today" ? 'selected' : '' }}>Hari Ini</option>
                            @endif
                        </select>
                    </div>
                    @if(Auth::user()->role->name != \App\Schemas\RoleSchema::OB)
                    <div class="col-12 col-md-3">
                        <select class="form-control" id="user" name="user">
                            <option value="">Select User</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->name }}" {{ request('user') == $user->name ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-12 col-md-2">
                        <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                    </div>
                    <div class="col-12 col-md-auto mt-2">
                        <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                        <button type="button" onclick="window.location.href='{{ route('cctv-check.index') }}?task=all'" class="btn btn-secondary"><i class="fa fa-times"></i> Show All</button>
                    </div>
                </div>
            </form>
            @endcanAccess
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Petugas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checks as $check)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($check->date)->format('d-m-Y') }}</td> 
                            <td>
                                {{ $check->time }}
                            </td>
                            <td>
                                {{ $check->user ? $check->user->name : '' }}
                            </td> 
                            <td>
                                <form action="{{ route('cctv-check.destroy', $check->slug) }}" method="POST" style="display: inline-block;">
                                    @canAccess('show','cctv_checks')
                                    <a href="{{ route('cctv-check.show', $check->slug) }}" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                                    @endcanAccess
                                    @csrf
                                    @method('DELETE')
                                    @canAccess('destroy','cctv_checks')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                                    @endcanAccess
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $checks->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

@stop
@section('css')
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
    </style>
@stop
