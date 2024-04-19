@extends('adminlte::page')

@section('content_header')
    <h1>Daftar Peralatan</h1>
@stop

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Peralatan Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Peralatan Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Peralatan Berhasil Terhapus</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<div class="container p-3">
    @canAccess('create','equipment')
    <a href="{{ route('equipment.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Perlengkapan</a>
    @endcanAccess

    <table class="table table-bordered mt-5">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Total Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($equipments as $equipment)
            <tr>
                <td>{{ $equipment->code }}</td>
                <td>{{ $equipment->name }}</td>
                <td>{{ $equipment->total_stock }}</td>
                <td>
                    @canAccess('history','equipment')
                    <a href="{{ route('equipment.history', $equipment->slug) }}" class="btn btn-info">
                        <i class="fa fa-history" aria-hidden="true"></i>
                    </a>
                    @endcanAccess
                    @canAccess('edit','equipment')
                    <a href="{{ route('equipment.edit', $equipment->slug) }}" class="btn btn-info">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </a>
                    @endcanAccess
                    @canAccess('show','equipment')
                    <a href="{{ route('equipment.show', $equipment->slug) }}" class="btn btn-primary">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </a>
                    @endcanAccess

                    @canAccess('destroy','equipment')
                    <form action="{{ route('equipment.destroy', $equipment->slug) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                    @endcanAccess
                </td>
            </tr>
            @endforeach
        </tbody>
    </table> 

    {{ $equipments->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>
@endsection
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
@stop
@section('css')
<style>
   body {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
        }
        

</style>
@stop
