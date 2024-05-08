@extends('adminlte::page')

@section('content_header')
    <h1>Detail Perlengkapan: {{ $equipment->name }}</h1>
@stop

@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Perlengkapan</h5>
        </div>
        <div class="card-body bg-light">
            <h6 class="card-title"><strong>Kode:</strong> {{ $equipment->code }}</h6>
            <p class="card-text"><strong>Perlengkapan:</strong> {{ $equipment->name }}</p>
            <p class="card-text"><strong>Stok Tersedia:</strong> {{ $equipment->total_stock }}</p>
        </div>

        @if($equipment->equipmentReduction->isNotEmpty())
        <div class="card-footer bg-white">
            <h5 class="text-secondary">Jenis Pengurangan dan Jumlah Penggunaan</h5>
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Jenis Pengurangan</th>
                        <th>Stok Digunakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipment->equipmentReduction->groupBy('reduction_id') as $reduction_id => $reductions)
                    @php
                    $reductionType = $reductions->first()->reduction->name;
                    $totalUsedStock = $reductions->sum('stock');
                    @endphp
                    <tr>
                        <td>{{ $reductionType }}</td>
                        <td>{{ $totalUsedStock }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
