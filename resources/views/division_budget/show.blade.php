@extends('adminlte::page')

@section('title', 'Detail Anggaran Divisi')


@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('division-budget.index') }}">Pengajuan Anggaran</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $divisionBudget->name ?? '' }}</li>
    </ol>
</nav>
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">{{ $divisionBudget->name }}</h3>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Divisi:</strong> {{ $divisionBudget->division->name }}</p>
                <p><strong>Nama Anggaran:</strong> {{ $divisionBudget->name }}</p>
                <p><strong>Persentase Penyerapan:</strong> {{ $divisionBudget->budget_usage_percentage }}%</p>
            </div>
            <div class="col-md-6">
                <p><strong>Anggaran Awal:</strong> Rp {{ number_format($divisionBudget->initial_budget, 0, ',', '.') }}</p>
                <p><strong>Sisa Anggaran:</strong> Rp {{ number_format($divisionBudget->amount, 0, ',', '.') }}</p>
            </div>
        </div>

        <h4 class="mb-3">Quotes</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No Quote</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($divisionBudget->quotes as $quote)
                    <tr>
                        <td>{{ $quote->number_result }}</td>
                        <td>Rp {{ number_format($quote->total, 0, ',', '.') }}</td>
                        <td>
                            <a target="_blank" href="{{ route('quote.download.pdf', $quote->slug) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> Lihat Quote
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($divisionBudget->quotes->isEmpty())
        <div class="alert alert-info mt-3">
            Tidak ada quotes yang terkait dengan anggaran ini.
        </div>
        @endif
    </div>
</div>
@endsection

@section('css')
<style>
    .card {
        border-radius: 10px;
    }
    .table thead th {
        background-color: #343a40;
        color: #fff;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }
    .table-responsive {
        margin-top: 20px;
    }
    .card-header {
        border-radius: 10px 10px 0 0;
    }
    .alert-info {
        border-radius: 10px;
    }
</style>
@stop
