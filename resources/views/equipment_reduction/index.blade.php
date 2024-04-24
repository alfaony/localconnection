@extends('adminlte::page')

@section('content_header')
    <h1>Pengeluaran Perlengkapan</h1>
@stop
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Pengeluaran Peralatan Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Pengeluaran Peralatan Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Pengeluaran Peralatan Berhasil Terhapus</div>
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
    @canAccess('store','equipment_reductions')
    <a href="{{ route('equipment-reduction.create') }}" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Pengeluaran Perlengkapan</a>
    @endcanAccess
    @canAccess('index','equipment_reductions')
    <form action="{{ route('equipment-reduction.index') }}" method="get">
        <div class="d-flex flex-row-reverse">
            <div class="p-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>

            <div class="p-2">
            @php
                $order = request('order', 'desc');
            @endphp
                <select name="order" class="form-control">
                    <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Date </option>
                    <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Date </option>
                </select>
            </div>
            <div class="p-2">
            <select class="form-control" id="equipment_id" name="equipment_id">
                <option selected disabled>Peralatan</option>
                @foreach ($equipments as $equipment)
                    <option value="{{ $equipment->id }}" data-totalstock="{{ $equipment->total_stock }}" {{ (isset($reduction) && $reduction->equipment_id == $equipment->id) ? 'selected' : '' }}>
                        {{ $equipment->name }}
                    </option>
                @endforeach
            </select>
            </div>
            <div class="p-2">
                <select class="form-control" id="reduction_id" name="reduction_id">
                    <option selected disabled>Status</option>
                    @foreach ($reductions as $type)
                        <option value="{{ $type->id }}" {{ (isset($reduction) && $reduction->reduction_id == $type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
    @endcanAccess

    <!-- Tampilkan pesan sukses -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Perlengkapan</th>
                <th>Jenis Pengurangan</th>
                <th>Stok Digunakan</th>
                <th>Laporan</th>
                <th>Temuan</th>
                <th>Tindakan</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipmentReductions as $reduction)
            <tr>
                <td>{{ \Carbon\Carbon::parse($reduction->date)->format('Y-m-d') }}</td>
                <td>{{ $reduction->equipment->name }}</td>
                <td>{{ $reduction->reduction->name }}</td>
                <td>{{ $reduction->stock }}</td>
                <td>{{ $reduction->report }}</td>
                <td>{{ $reduction->found }}</td>
                <td>{{ $reduction->doing }}</td>
                <td>
                    
                    <form action="{{ route('equipment-reduction.destroy', $reduction->slug) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                        @canAccess('store','equipment_reductions')
                        <a href="{{ route('equipment-reduction.edit', $reduction->slug) }}" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                    @canAccess('destroy','equipment_reductions')
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                    @endcanAccess
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {!! $equipmentReductions->links() !!}
    </div>
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