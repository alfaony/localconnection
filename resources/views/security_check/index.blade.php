@extends('adminlte::page')
@section('content_header')
    <h2>Kontrol Keamanan</h2>
@stop
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Kontrol Keamanan Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Kontrol Keamanan Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Kontrol Keamanan Berhasil Terhapus</div>
    @endif

</div>

<div class="container p-3">
    <div class="card">
        <div class="card-body">
            @canAccess('create','security_checks')
            @if(!@$today->clock_in)
            <a href="{{ route('security-check.create') }}" class="btn btn-primary mb-3"> Kontrol Pagi</a>
            @elseif(!@$today->clock_out)
            <a href="{{ route('security-check.edit',$today->slug) }}" class="btn btn-primary mb-3"> Kontrol Sore</a>
            @else
            <span class="badge badge-danger">Selesai Melakukan Kontrol</span>
            @endif
            @endcanAccess
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kontrol Pagi</th>
                            <th>Kontrol Sore</th>
                            @if(Auth::user()->role->name == \App\Schemas\RoleSchema::BM)
                            <th>Petugas</th>
                            <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checks as $check)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($check->date)->format('d-m-Y') }}</td> 
                            <td>
                                @canAccess('show','security_checks')
                                @if($check->clock_in)
                                <a href="{{ route('security-check.show', $check->slug) }}?type=check_in" >{{ $check->clock_in }}</a>
                                @endif
                                @endcanAccess
                            </td>
                            <td>
                                @canAccess('show','security_checks')
                                @if($check->clock_out)
                                <a href="{{ route('security-check.show', $check->slug) }}?type=check_out" >{{ $check->clock_out }}</a>
                                @else
                                    Belum Tersedia
                                @endif
                                @endcanAccess
                            </td>
                            @if(Auth::user()->role->name == \App\Schemas\RoleSchema::BM)
                            <td>
                                {{ $check->user ? $check->user->name : '' }}
                            </td> 
                            <td>
                                @canAccess('destroy','security_checks')
                                <form action="{{ route('security-check.destroy', $check->slug) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                                </form>
                                @endcanAccess
                            </td>
                            @endif
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
