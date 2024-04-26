@extends('adminlte::page')

{{-- @section('title', 'User') --}}

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="container">
    @canAccess('showReport','homes')
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Proyek Aktif</h5>
                    <p class="card-text">{{ $totalActiveProjects }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Pekerja Aktif</h5>
                    <p class="card-text">{{ $totalActiveWorkers }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Anggaran Pembelian</h5>
                    <p class="card-text">{{ 'Rp. '.number_format($totalPurchaseBudget,0,',','.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Proyek Aktif</h5>
                    <p class="card-text">{{ 'Rp. '.number_format($activeProjectsBudget,0,',','.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Anggaran Pekerja</h5>
                    <p class="card-text">{{ 'Rp. '.number_format($activeEmployeeBudget,0,',','.') }}</p>
                    
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Quote</h5>
                    <p class="card-text">{{ $totalQuote }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total SPK</h5>
                    <p class="card-text">{{ $totalWorkOrder }}</p>
                </div>
            </div>
        </div>
    </div>
    @endcanAccess
</div>

@if(Auth::user()->role->name == \App\Schemas\RoleSchema::BM)
<div class="container">
    <div class="py-4">
        <h2>Perlengkapan Stok Habis</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipments as $equipment)
                        <tr>
                            <td>{{ $equipment->name }}</td>
                            <td>{{ $equipment->code }}</td>
                            <td>{{ $equipment->total_stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
@stop
