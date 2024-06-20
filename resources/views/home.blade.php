@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')

<div class="col-md-12 mt-2">
    @if(Session::get('updateProfile'))
    <div class="alert alert-success mt-3">Pengguna Berhasil Perbarui</div>
    @endif
</div>
@canAccess('showReport','homes')
<div class="card py-3">
    <div class="card-header">
        <h5>Laporan Overview Proyek</h5>
    </div>
    <div class="card-body">
        <div class="row">
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
                        <h5 class="card-title">Anggaran Proyek Aktif</h5>
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
        
        <div class="row mt-3">
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
    </div>
    <div class="card-body">
        <div class="card-header">
            <h5> Quote Tanpa SPK </h5>
        </div>
        <!-- Add Search Form -->
        <form method="GET" action="{{ route('home') }}" class="mb-3">
            <div class="row mt-2 align-items-center">
                <div class="col-auto">
                    <input type="text" name="search_quote" class="form-control" placeholder="Cari No Quote">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>No Quote</th>
                        <th>Total</th>
                        @canAccess('downloadPdf','quotes')
                        <th>Aksi</th>
                        @endcanAccess
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotesWithoutWorkOrder as $quote)
                    <tr>
                        <td>{{ $quote->number_result }}</td>
                        <td>Rp {{ number_format($quote->total, 0, ',', '.') }}</td>
                        @canAccess('downloadPdf','quotes')
                        <td>
                            <a href="{{ route('quote.download.pdf', $quote->slug) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> Quote
                            </a>
                        </td>
                        @endcanAccess
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada quotes tanpa WorkOrder.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $quotesWithoutWorkOrder->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@endcanAccess
@canAccess('showReportPointDaily','homes')
<div class="card py-3">
    <div class="card-header">
        <h5>Laporan Overview Pekerjaan Harian</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <!-- Date filters -->
                <form method="GET" action="{{ route('home') }}" class="mb-3">
                    <div class="mb-4 row">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Tanggal Mulai:</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" value="{{ request('start_date') ?? $startDate->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Tanggal Akhir:</label>
                            <input type="date" class="form-control" name="end_date" id="end_date" value="{{ request('end_date')  ?? $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                            <button type="button" onclick="window.location.href='{{ route('home') }}'" class="btn btn-secondary"><i class="fa fa-times"></i> Reset</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Poin Tugas</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskPoints }} Poin</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Poin Training</div>
                    <div class="card-body">
                        <p class="card-text">{{ $trainingPoints }} Poin</p>
                    </div>
                </div>
            </div>
            @if(Auth::user()->role->name == \App\Schemas\RoleSchema::SALES)
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Poin Penjualan</div>
                    <div class="card-body">
                        <p class="card-text">{{ $ipRightPoints }} Poin</p>
                    </div>
                </div>
            </div>
            @else
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Poin Hak Cipta</div>
                    <div class="card-body">
                        <p class="card-text">{{ $ipRightPoints }} Poin</p>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-md-3">
                <div class="card bg-success mb-3">
                    <div class="card-header">Jumlah Tugas Diselesaikan</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskCompleteCount }} Tugas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Todo Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title">Todo</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTaskTodoCount }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Doing Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title">Doing</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTasDoingCount }}</p>
                    </div>
                </div>
            </div>

            <!-- In Review Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-warning">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title">In Review</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTaskInreviewCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Complete Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title">Complete</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTaskCompleteCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Not Complete Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title">Not Complete</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTaskNotComplateCount }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card py-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-header text-danger">Jumlah Tugas Overdue</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskCountOverdue }} Tugas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-header text-primary">Jumlah Hari Ini</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskCountToday }} Tugas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-header text-green">Jumlah Tugas Mendatang</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskCountUpcoming }} Tugas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endcanAccess




@if(Auth::user()->role->name == \App\Schemas\RoleSchema::BM)
<div class="row">
    <div class="py-4">
        <h2>Perlengkapan Stok Habis</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Perlengkapan</th>
                        <th>Kode Perlengkapan</th>
                        <th>Stok Tersedia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipments as $equipment)
                        <tr>
                            <td>{{ $equipment->name }}</td>
                            <td>{{ $equipment->code }}</td>
                            <td>{{ $equipment->total_stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data stok habis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script>
    $(document).ready(function () {
        $('input[name="start_date"]').on('change', function() {
            var startDateValue = $(this).val();
            $('input[name="end_date"]').val(startDateValue);
        });
    });
</script>
@stop

@section('css')
<style>
    .card-header {
        font-weight: bold;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .form-label {
        font-weight: bold;
    }
</style>

@endsection