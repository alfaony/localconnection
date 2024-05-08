@extends('adminlte::page')

@section('content_header')
    <h1>Pengeluaran Perlengkapan</h1>
@stop
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Pengeluaran Akses Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Pengeluaran Akses Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Pengeluaran Akses Berhasil Terhapus</div>
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
    <form action="{{ route('equipment-reduction.index') }}" method="get" class="mb-3">
    <div class="row align-items-end">
        <div class="col-sm-12 col-md-3 mb-3 mb-md-0">
            <select class="form-control" id="equipment_id" name="equipment_id">
                <option selected disabled>Perlengkapan</option>
                @foreach ($equipments as $equipment)
                    <option value="{{ $equipment->id }}" data-totalstock="{{ $equipment->total_stock }}" {{ (isset($reduction) && $reduction->equipment_id == $equipment->id) ? 'selected' : '' }}>
                        {{ $equipment->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-12 col-md-3 mb-3 mb-md-0">
            <select class="form-control" id="reduction_id" name="reduction_id">
                <option selected disabled>Status</option>
                @foreach ($reductions as $type)
                    <option value="{{ $type->id }}" {{ (isset($reduction) && $reduction->reduction_id == $type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-12 col-md-3 mb-3 mb-md-0">
            @php
                $order = request('order', 'desc');
            @endphp
            <select name="order" class="form-control">
                <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Date </option>
                <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Date </option>
            </select>
        </div>
        <div class="col-sm-12 col-md-3">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Search</button>
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
    <div class="table-responsive-md">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="w-10">Tanggal</th> <!-- Menggunakan width 10% dari tabel -->
                    <th class="w-15">Perlengkapan</th> <!-- Menggunakan width 15% dari tabel -->
                    <th class="w-15">Jenis Pengurangan</th> <!-- Menggunakan width 15% dari tabel -->
                    <th class="w-10">Stok Digunakan</th> <!-- Menggunakan width 10% dari tabel -->
                    <th style="width: 35%;">Laporan</th> <!-- Menggunakan inline style untuk lebar terbesar -->
                    <th class="w-15">Actions</th> <!-- Menggunakan width 15% dari tabel -->
                </tr>
            </thead>
            <tbody>
                @foreach($equipmentReductions as $reduction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($reduction->date)->format('d-m-Y') }}</td>
                    <td>{{ $reduction->equipment->name }}</td>
                    <td>{{ $reduction->reduction->name }}</td>
                    <td>{{ $reduction->stock }}</td>
                    <td>{!! $reduction->report !!}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                @canAccess('show','equipment_reductions')
                                <li><a class="dropdown-item btn btn-sm btn-primary" href="{{ route('equipment-reduction.show', $reduction->slug) }}"><i class="fa fa-eye"></i> View</a></li>
                                @endcanAccess
                                @canAccess('edit','equipment_reductions')
                                <li><a class="dropdown-item btn btn-sm btn-primary" href="{{ route('equipment-reduction.edit', $reduction->slug) }}"><i class="fa fa-edit"></i> Edit</a></li>
                                @endcanAccess
                                @canAccess('destroy','equipment_reductions')
                                <li>
                                    <form action="{{ route('equipment-reduction.destroy', $reduction->slug) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</button>
                                    </form>
                                </li>
                                @endcanAccess
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $equipmentReductions->withQueryString()->links('vendor.pagination.bootstrap-4') }}
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