@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
<div class="row g-3">
    <div class="col-md-12 mt-2">
        @if(Session::get('updateProfile'))
        <div class="alert alert-success mt-3">Pengguna Berhasil Perbarui</div>
        @endif

        @canAccess('reminderDashboard', 'weekly_reports')
        <div id="weekly-report-reminder">
            <div class="text-center my-3">
                <i class="fas fa-spinner fa-spin text-muted"></i> Memeriksa laporan mingguan...
            </div>
        </div>
        @endcanAccess

        <div id="vehicle-reminder-pic"></div>
        <div id="vehicle-reminder-manager"></div>

        <div id="reminder-letter-pic"></div>
        <div id="reminder-letter-manager"></div>
    </div>

    @canAccess('dashboardReport','homes')
    <!-- Profile and Stats -->
    <div class="col-md-3 mt-3">
        <div class="card border-0 shadow-lg hover-effect">
            <div class="card-body text-center p-4">
                <!-- Profile Image -->
                <div class="avatar-wrapper mb-4">
                    <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://placehold.co/600x400?text=Your%20Avatar' }}" class="rounded-circle shadow-sm" alt="User Image"
                        style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #fff">
                </div>

                <!-- Profile Info -->
                <div class="profile-meta">
                    <h4 class="mb-3 fw-bold text-gradient">
                        <i class="bi bi-person-gear me-2"></i>{{  Auth::user()->name }}
                    </h4>

                    <!-- Badge -->
                    <div
                        class="status-badge bg-soft-warning d-inline-flex align-items-center py-2 px-3 mb-3 rounded-pill">
                        <i class="bi bi-shield-check me-2 text-warning"></i>
                        <span class="text-dark small fw-medium"> {{  ucfirst(Auth::user()->role->name) }}</span>
                    </div>

                    <!-- Score -->
                    <div class="score-container bg-soft-success p-3 rounded-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="bi bi-trophy-fill me-2 text-gradient-gold fs-5"></i>
                            <div>
                                <div class="text-muted small">CURRENT SCORE</div>
                                <div class="h4 mb-0 fw-bold text-success" id="currentScore">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcanAccess
    
    <div class="col-md-9 mt-3">
        <!-- Info Cards Section -->
        <div class="row g-3">
            {{-- 
            <div class="col-md-3">
                <div class="card info-box border-0 shadow-sm h-100 hover-effect"
                    style="pointer-events: none; opacity: 0.5;">
                    <div class="card-body d-flex align-items-center p-3">
                        <div class="icon-container bg-primary-soft rounded-circle p-3 me-3">
                            <i class="bi bi-shield-check text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">GUILD</div>
                            <div class="h4 mb-0 text-primary fw-bold">Overlord</div>
                        </div>
                    </div>
                </div>
            </div>
            --}}
            @canAccess('dashboardReport','homes')
            <div class="col-md-4">
                <div class="card info-box border-0 shadow-sm h-100 hover-effect">
                    <div class="card-body d-flex align-items-center p-3">
                        <div class="icon-container bg-success-soft rounded-circle p-3 me-3">
                            <i class="bi bi-check2-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">TASK COMPLETE</div>
                            <div class="h4 mb-0 text-success fw-bold" id="totalTasksComplete">
                                <span class="placeholder col-8 placeholder-glow"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card info-box border-0 shadow-sm h-100 hover-effect">
                    <div class="card-body d-flex align-items-center p-3">
                        <div class="icon-container bg-info-soft rounded-circle p-3 me-3">
                            <i class="bi bi-calendar-check text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">CHECK-INS</div>
                            <div class="h4 mb-0 text-info fw-bold" id="checkin_point_percentage">
                                <span class="placeholder col-8 placeholder-glow"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card info-box border-0 shadow-sm h-100 hover-effect">
                    <div class="card-body d-flex align-items-center p-3">
                        <div class="icon-container bg-danger-soft rounded-circle p-3 me-3">
                            <i class="bi bi-graph-up text-danger fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">TOTAL POINTS</div>
                            <div class="h4 mb-0 text-danger fw-bold" id="totalPoints">
                                <span class="placeholder col-8 placeholder-glow"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcanAccess

            @canAccess('infoApprovementHr', 'dayoffs')
            <div class="col-md-6 mt-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Cuti yang Menunggu Persetujuan HR
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="count-hr">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </div>
                        </div>
                        <div><i class="fas fa-user-tie fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
            @endcanAccess
            @canAccess('infoApprovementFinance', 'dayoffs')
            <div class="col-md-6 mt-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Cuti yang Menunggu Persetujuan Finance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="count-finance">
                                <span class="spinner-border spinner-border-sm text-info"></span>
                            </div>
                        </div>
                        <div><i class="fas fa-coins fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
            @endcanAccess
        </div>


        <!-- Action Cards Section -->
         @canAccess('meetingAgenda','homes')
        <div class="card border-0 shadow-sm mt-2">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title flex-grow-1">
                    Agenda Meeting
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-12">
                        <table class="table table-sm table-bordered" id="agenda-table">
                            <thead>
                                <tr>
                                    <th>Agenda</th>
                                    <th>Tanggal</th>
                                    <th>Pukul</th>
                                    <th>Type</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcanAccess
    </div>
</div>

<div class="row g-3">
    <!-- Rankings -->
    @canAccess('leaderboard','homes')
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-trophy me-2"></i>Ranking Top Score
            </div>
            <div class="card-body" style="height: 240px; overflow-y: auto;">
                <div id="leaderboard-loader" class="d-flex justify-content-center align-items-center"
                    style="height: 180px;">
                    <div class="spinner-border text-success" role="status"></div>
                </div>
                <ol class="list-group list-group-flush d-none" id="leaderboard-list">
                    <!-- Data akan diisi via JS -->
                </ol>
            </div>
        </div>
    </div>
    @endcanAccess

    @canAccess('overdueRanking','homes')
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-clipboard-check"></i> Ranking Staff Overdue to Waiting Review
            </div>
            <div class="card-body" style="height: 240px; overflow-y: auto;">
                <div id="overdue-inreview-loader" class="d-flex justify-content-center align-items-center"
                    style="height: 180px;">
                    <div class="spinner-border text-warning" role="status"></div>
                </div>
                <ol class="list-group list-group-flush d-none" id="overdue-inreview-ranking">
                    <!-- Data akan diisi via JS -->
                </ol>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2 mr-1"></i>Ranking Staff Overdue Task
            </div>
            <div class="card-body" style="height: 240px; overflow-y: auto;">
                <div id="overdue-loader" class="d-flex justify-content-center align-items-center"
                    style="height: 180px;">
                    <div class="spinner-border text-danger" role="status"></div>
                </div>
                <ol class="list-group list-group-flush d-none" id="overdue-ranking">
                    <!-- Data akan diisi via JS -->
                </ol>
            </div>
        </div>
    </div>
    @endcanAccess
    
    @canAccess('listDayoff','homes')
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-user-clock mr-2"></i>User Cuti Hari Ini
            </div>
            <div class="card-body" id="cuti-today-container">
                <div class="text-center text-muted">
                    <i class="fas fa-spinner fa-spin"></i> Memuat data cuti...
                </div>
            </div>
        </div>
    </div>
    @endcanAccess

</div>

<div class="card shadow-sm">
    <div class="card-body">
        @canAccess('index','office_media')
        {{-- What's Happening Now --}}
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 mt-3">
                <h5>What's Happening Now !</h5>
                @canAccess('store','office_media')
                <button class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#uploadMomentModal">Upload</button>
                @endcanAccess
            </div>
        
        
            <div style="height: 300px; overflow-y: auto;" id="office-media-image-section">
                <div class="loading-spiner-office d-flex justify-content-center align-items-center" style="height: 300px; display: none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        
            {{-- Youtube Embed Section --}}
            <hr>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Nonton Youtube Kantor ( Embed URL )</h5>
                @canAccess('store','office_media')
                <button class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#youtubeEmbedModal">Embed URL</button>
                @endcanAccess
            </div>
        
            <div style="height: 300px; overflow-y: auto;" id="office-media-youtube-section">
                <div class="loading-spiner-office d-flex justify-content-center align-items-center" style="height: 300px; display: none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        @endcanAccess
    </div>
</div>
<!-- End Rankings -->

<div class="row">
    <div class="col-md-12">
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
                        <a href="{{ route('quote.index') }}">
                            <div class="card text-white bg-warning mb-3 hover-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Quote</h5>
                                    <p class="card-text">{{ $totalQuote }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('work-order.index') }}">
                            <div class="card text-white bg-warning mb-3 hover-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total SPK</h5>
                                    <p class="card-text">{{ $totalWorkOrder }}</p>
                                </div>
                            </div>
                        </a>
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
                                    <a href="{{ route('quote.download.pdf', $quote->slug) }}"
                                        class="btn btn-sm btn-primary">
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
                                    <input type="date" class="form-control" name="start_date" id="start_date"
                                        value="{{ request('start_date') ?? $startDate->format('Y-m-d') }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">Tanggal Akhir:</label>
                                    <input type="date" class="form-control" name="end_date" id="end_date"
                                        value="{{ request('end_date')  ?? $endDate->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-12 mt-2">
                                    <button type="submit" class="btn btn-info"><i class="fa fa-search"></i>
                                        Cari</button>
                                    <button type="button" onclick="window.location.href='{{ route('home') }}'"
                                        class="btn btn-secondary"><i class="fa fa-times"></i> Reset</button>
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
                    <div class="col-md3">
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
    </div>
</div>


@canAccess('showScheduleOb','homes')
<div class="card py-3">
    <div class="card-body">
        <div id="calendar"></div>
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

<!-- Modal -->
@canAccess('store','office_media')
{{-- Modal Upload Moment --}}
<div class="modal fade" id="uploadMomentModal" tabindex="-1" role="dialog" aria-labelledby="uploadMomentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('office-media.store') }}" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Upload Moment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span> {{-- X Button --}}
                </button>
            </div>
            <input type="hidden" name="type" value="image">
            <div class="modal-body">
                <div class="mb-3">
                    <label for="moment_caption" class="form-label">Caption</label>
                    <input type="text" class="form-control" id="moment_caption" placeholder="Misalnya: Ultah Kevin!">
                </div>
                <div class="mb-3">
                    <label for="moment_photo" class="form-label">Foto</label>
                    <input class="form-control" type="file" id="moment_photo" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Upload Sekarang</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Youtube Embed --}}
<div class="modal fade" id="youtubeEmbedModal" role="dialog"  tabindex="-1" aria-labelledby="youtubeEmbedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Embed YouTube</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span> {{-- X Button --}}
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="youtube_url" class="form-label">YouTube URL</label>
                    <input type="url" class="form-control" id="youtube_url" placeholder="https://youtube.com/embed/abc123">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_temporary" name="is_temporary" checked>
                        <label class="form-check-label" for="is_temporary">
                            Temporary
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="video_caption" class="form-label">Caption</label>
                    <input type="text" class="form-control" id="video_caption" placeholder="Misalnya: Video Training Sales">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endcanAccess

@canAccess('index','office_media')
{{-- Modal Fullscreen Bootstrap Carousel --}}
<div class="modal fade" id="officeMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
      <div class="modal-body p-0">
        <div id="officeMediaCarousel" class="carousel slide">
            <div class="carousel-inner" id="officeMediaCarouselInner">
                <!-- Slides will be injected -->
                 <button class="carousel-control-prev" type="button" data-bs-target="#officeMediaCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
  </button>

  <button class="carousel-control-next" type="button" data-bs-target="#officeMediaCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
  </button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endcanAccess
@endsection
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.2/main.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> 

@canAccess('meetingAgenda','homes')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const tableBody = document.querySelector('#agenda-table tbody');

        // Tampilkan indikator loading
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <div class="spinner-border text-primary" role="status">
                    </div>
                </td>
            </tr>
        `;

        try {
            const res = await fetch("{{ route('home.meetingAgenda') }}");
            const data = await res.json();

            // Kosongkan dulu tbody
            tableBody.innerHTML = '';

            if (data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No agendas found.</td></tr>';
                return;
            }

            for (const item of data) {
                console.log(item);

                const row = `
                    <tr>
                        <td>${item.meeting_name}</td>
                        <td>${formatDate(item.start_date)}</td>
                        <td>${item.start_time} - ${item.end_time}</td>
                        <td><span class="badge bg-${item.meeting_type === 'online' ? 'info' : 'secondary'}">${item.meeting_type}</span></td>
                        <td>${item.meeting_type === 'online' ? `<a href="${item.google_meet_link}" target="_blank" class="text-primary">Google Meet</a>` : item.meeting_location}</td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            }
        } catch (err) {
            console.error(err);
            tableBody.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Failed to load agendas.</td></tr>';
        }

        function formatDate(dateStr) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateStr).toLocaleDateString('id-ID', options);
        }

        function statusColor(status) {
            return status === 'approved' ? 'success' : (status === 'pending' ? 'warning' : 'danger');
        }
    });
</script>
@endcanAccess

@canAccess('infoPic','subscribe_letters')
<script>
    async function loadLetterReminderPIC() {
        const container = document.querySelector('#reminder-letter-pic');
        container.innerHTML = `<div class="text-center py-3">
            <i class="fas fa-spinner fa-spin"></i> Memuat pengingat surat (PIC)...
        </div>`;

        try {
            const response = await fetch("{{ route('reminder.letter.pic') }}");
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            console.error(error);
            container.innerHTML = `<div class="alert alert-danger">❌ Gagal memuat pengingat surat (PIC).</div>`;
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        loadLetterReminderPIC();
    });
</script>
@endcanAccess

@canAccess('infoManager','subscribe_letters')
<script>
    async function loadLetterReminderManager() {
        const container = document.querySelector('#reminder-letter-manager');
        container.innerHTML = `<div class="text-center py-3">
            <i class="fas fa-spinner fa-spin"></i> Memuat pengingat surat (Manager)...
        </div>`;

        try {
            const response = await fetch("{{ route('reminder.letter.manager') }}");
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            console.error(error);
            container.innerHTML = `<div class="alert alert-danger">❌ Gagal memuat pengingat surat (Manager).</div>`;
        }
    }

    document.addEventListener("DOMContentLoaded", () => 
    {
        loadLetterReminderManager();
    });
</script>
@endcanAccess

@canAccess('infoManager','vehicles')
<script>
    async function loadVehicleReminderManager(containerSelector = '#vehicle-reminder-manager') {
        const container = document.querySelector(containerSelector);
        const url = "{{ route('reminder.vehicle.manager') }}";

        container.innerHTML = `<div class="text-center py-3">
            <i class="fas fa-spinner fa-spin"></i> Memuat pengingat kendaraan (Manager)...
        </div>`;

        try {
            const response = await fetch(url);
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            console.error(error);
            container.innerHTML = `<div class="alert alert-danger">❌ Gagal memuat pengingat kendaraan (Manager).</div>`;
        }
    }

    // Panggil sesuai kebutuhan (bisa 1 atau dua-duanya)
    document.addEventListener("DOMContentLoaded", () => {
        loadVehicleReminderManager();
    });
</script>
@endcanAccess

@canAccess('infoPic','vehicles')
<script>
    async function loadVehicleReminderPIC(containerSelector = '#vehicle-reminder-pic') {
        const container = document.querySelector(containerSelector);
        const url = "{{ route('reminder.vehicle.pic') }}";

        container.innerHTML = `<div class="text-center py-3">
            <i class="fas fa-spinner fa-spin"></i> Memuat pengingat kendaraan (PIC)...
        </div>`;

        try {
            const response = await fetch(url);
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            console.error(error);
            container.innerHTML = `<div class="alert alert-danger">❌ Gagal memuat pengingat kendaraan (PIC).</div>`;
        }
    }

    // Panggil sesuai kebutuhan (bisa 1 atau dua-duanya)
    document.addEventListener("DOMContentLoaded", () => {
        loadVehicleReminderPIC();
    });
</script>
@endcanAccess

@canAccess('listDayoff','homes')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.getElementById('cuti-today-container');
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-spinner fa-spin"></i> Memuat data cuti...
            </div>
        `;

        try {
            const response = await fetch("{{ route('home.listDayoff') }}");
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            container.innerHTML = `
                <div class="text-danger">
                    <i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat data cuti hari ini.
                </div>
            `;
        }
    });
</script>
@endcanAccess

@canAccess('reminderDashboard', 'weekly_reports')
<script>
    $(document).ready(function () {
        $.ajax({
            url: "{{ route('weekly-report.reminderDashboard') }}",
            method: "GET",
            success: function (res) {
                $('#weekly-report-reminder').html(res.html);
            },
            error: function () {
                $('#weekly-report-reminder').html(
                    '<div class="alert alert-danger">Gagal memuat reminder laporan mingguan.</div>'
                );
            }
        });
    });
</script>
@endcanAccess

@canAccess('infoApprovementHr', 'dayoffs')
<script>
    async function loadApprovalInfoHr() 
    {
        $('#count-hr').html('<span class="spinner-border spinner-border-sm text-primary"></span>');

        const response = await $.get("{{ route('dayoff.infoApprovementHr') }}");
        $('#count-hr').text(response.total);
    }

    $(document).ready(async function () {
        await loadApprovalInfoHr();
    });
</script>
@endcanAccess
@canAccess('infoApprovementFinance', 'dayoffs')
<script>
    async function loadApprovalInfoFinance() 
    {
        $('#count-finance').html('<span class="spinner-border spinner-border-sm text-info"></span>');

        const response = await $.get("{{ route('dayoff.infoApprovementFinance') }}");
        $('#count-finance').text(response.total);
    }

    $(document).ready(async function () {
        await loadApprovalInfoFinance();
    });
</script>
@endcanAccess

@canAccess('index','office_media')
<script>
        $(document).ready(function () 
        {
            loadOfficeMedia();
        });
        function loadOfficeMedia() 
        {
            $('#office-media-image-section, #office-media-youtube-section').html(`
                <div class="d-flex justify-content-center py-5">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            `);

            $.ajax({
                url: "{{ route('office-media.index') }}",
                type: "GET",
                success: function(res) {
                    
                    if (res.status === 'success') 
                    {                    
                        $('#office-media-image-section').html(res.data.image);
                        $('#office-media-youtube-section').html(res.data.youtube);
                    }
                },
                error: function() {
                    $('#office-media-image-section, #office-media-youtube-section').html(`<div class="text-center text-danger">Failed to load content.</div>`);
                }
            });

            $('#office_media_image').html(`
                <div class="col-md-3 mt-3">
                    <div class="card border-0">
                        <img src="https://picsum.photos/300/200" class="rounded-circle mx-auto d-block mt-2"
                                alt="moment image" style="object-fit: cover; width: 100px; height: 100px;">
                        <small class="mt-2 d-block mb-2">Caption dummy</small>
                    </div>
                </div>
            `);

        }
</script>
<script>
    async function handleMediaClick(clickedIndex) {
        const $allImages = $('.office-media-thumb');
        let slidesHtml = '';

        $allImages.each(function (index) {
            const imgUrl = $(this).data('url');
            const title = $(this).attr('alt') ?? '';
            const activeClass = (index === clickedIndex) ? 'active' : '';

            slidesHtml += `
                <div class="carousel-item ${activeClass}">
                    <div class="d-flex justify-content-center align-items-center">
                        <img src="${imgUrl}" class="d-block mx-auto img-fluid" alt="${title}" style="object-fit: contain;">
                    </div>
                </div>
                `;
        });

        // Inject HTML
        $('#officeMediaCarouselInner').html(slidesHtml);

        // Tunggu 1 tick agar DOM benar-benar render
        await new Promise(resolve => setTimeout(resolve, 10));

        // Init carousel
        const carouselElement = document.getElementById('officeMediaCarousel');
        if (carouselElement.carouselInstance) {
            carouselElement.carouselInstance.dispose();
        }

        carouselElement.carouselInstance = new bootstrap.Carousel(carouselElement, {
            interval: false,
    ride: false,
    keyboard: true,
        });

        carouselElement.carouselInstance.to(clickedIndex);

        // Tampilkan modal
        const modalElement = document.getElementById('officeMediaModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Modal #officeMediaModal not found in DOM');
        }
    }

    // Bind click
    $(document).on('click', '.office-media-thumb', function () {
        const clickedIndex = $(this).data('index');
        handleMediaClick(clickedIndex);
    });
</script>
@endcanAccess

@canAccess('store','office_media')
<script>
    $(document).ready(function () {
        loadOfficeMedia();
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        

        // 📸 Submit form Upload Image
        $('#uploadMomentModal form').on('submit', function (e) {
            e.preventDefault();

            let formData = new FormData();
            formData.append('type', 'image');
            formData.append('title', $('#moment_caption').val());
            formData.append('file', $('#moment_photo')[0].files[0]);

            
            $.ajax({
                url: "{{ route('office-media.store') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('#uploadMomentModal button[type="submit"]').prop('disabled', true).text('Uploading...');
                },
                success: function (response) 
                {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Media berhasil diupload.',
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        didOpen: () => 
                        {
                            Swal.showLoading()
                        },
                        willClose: () => 
                        {
                            loadOfficeMedia();
                            $('#uploadMomentModal form')[0].reset();
                            $('#uploadMomentModal .close').click();
                        }
                    });
                },
                error: function (xhr) {
                    console.log(xhr);
                    
                    alert(xhr.responseJSON?.message || 'Gagal upload foto');
                },
                complete: function () {
                    $('#uploadMomentModal button[type="submit"]').prop('disabled', false).text('Upload Sekarang');
                }
            });
        });

        // 🎥 Submit form YouTube URL
        $('#youtubeEmbedModal form').on('submit', function (e) {
            e.preventDefault();

            let youtubeUrl = $('#youtube_url').val();
            let caption = $('#video_caption').val();
            let isTemporary = $('#is_temporary').is(':checked');

            $('#youtubeEmbedModal .close').click();

            $.ajax({
                url: "{{ route('office-media.store') }}",
                method: "POST",
                data: {
                    type: 'youtube',
                    youtube_url: youtubeUrl,
                    title: caption,
                    is_temporary: isTemporary ? 1 : 0
                },
                success: function (response) {
                    Swal.fire({
                        title: 'Success',
                        text: 'Video berhasil disimpan',
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading()
                        },
                        willClose: () => {
                            loadOfficeMedia();
                        }
                    });
                    
                    $('#youtubeEmbedModal form')[0].reset();
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Gagal menyimpan video');
                }
            });
        });
    });
</script>
@endcanAccess

@canAccess('destroy','office_media')
<script>
    $(document).on('click', '.delete-media-btn', function () 
    {
        const mediaId = $(this).data('id');
        if (!confirm('Yakin ingin menghapus media ini?')) return;
    
        $.ajax({
            url: `{{ route('office-media.destroy', ':id') }}`.replace(':id', mediaId),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (res) {
                if (res.status === 'success') {
                    
                    loadOfficeMedia(); // reload section
                }
            },
            error: function () {
                alert('Gagal menghapus media.');
            }
        });
    });
</script>
@endcanAccess

@canAccess('overdueRanking','homes')
<script>
    async function loadOverdueLeaderboard() 
    {
        $('#overdue-loader').removeClass('d-none');
        $('#overdue-ranking').addClass('d-none');

        $('#overdue-inreview-loader').removeClass('d-none');
        $('#overdue-inreview-ranking').addClass('d-none');
        
        const response = await $.get('{{ route("home.overdueRanking") }}');
        
        const container = $('#overdue-ranking');
        const containerInreview = $('#overdue-inreview-ranking');

        container.empty();
        containerInreview.empty();
        
        if (response.data.length === 0) {
            container.append(`<li class="list-group-item text-center text-muted">No data available</li>`);
            containerInreview.append(`<li class="list-group-item text-center text-muted">No data available</li>`);
        } else {
            if(response.status === 'success') 
            {
                if(response.data && response.data.overdueUsers.length > 0)
                {
                    response.data.overdueUsers.forEach((user, index) => {
                        container.append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    ${index + 1 <= 9 ? `<i class="bi bi-${index + 1}-circle-fill text-danger me-2"></i>` : `<span class="badge bg-danger text-dark">${index + 1}</span>`}
                                    ${user.name}
                                </span>
                                <span class="badge bg-danger">${user.overdue_count}</span>
                            </li>
                        `);
                    });
                }
                else
                {
                    container.append(`<li class="list-group-item text-center text-muted">No data available</li>`);
                }

                if(response.data && response.data.overdueInReviewUsers.length > 0)
                {
                    
                    response.data.overdueInReviewUsers.forEach((user, index) => {
                        containerInreview.append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    ${index + 1 <= 9 ? `<i class="bi bi-${index + 1}-circle-fill text-warning me-2"></i>` : `<span class="badge bg-warning text-dark">${index + 1}</span>`}
                                    ${user.name}
                                </span>
                                <span class="badge bg-warning">${user.overdue_count}</span>
                            </li>
                        `);
                    });
                }
                else
                {
                    containerInreview.append(`<li class="list-group-item text-center text-muted">No data available</li>`);
                }
            }
        }

        $('#overdue-loader').removeClass('d-flex').removeClass('justify-content-center').removeClass('align-items-center');
        $('#overdue-loader').addClass('d-none');

        $('#overdue-inreview-loader').removeClass('d-flex').removeClass('justify-content-center').removeClass('align-items-center');
        $('#overdue-inreview-loader').addClass('d-none');

        container.removeClass('d-none');
        containerInreview.removeClass('d-none');
    }

    $(document).ready(function () {
        loadOverdueLeaderboard();
    });
</script>
@endcanAccess

@canAccess('leaderboard','homes')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchLeaderboard();
    });

    async function fetchLeaderboard() {
        try {
            const response = await $.ajax({
                url: '{{ route("home.leaderboard") }}',
                method: 'GET',
            });

            const leaderboard = response.data;
            const list = $('#leaderboard-list');
            list.empty();

            if (leaderboard.length === 0) {
                list.append(`<li class="list-group-item text-center text-muted">No data available</li>`);
            } else {
                leaderboard.forEach((item, index) => {
                    const icon = index + 1 <= 9 ?
                        `<i class="bi bi-${index + 1}-circle-fill text-success me-2"></i>` :
                        `<span class="badge bg-success text-dark">${index + 1}</span>`;
                    const html = `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${icon}${item.name}</span>
                                <span class="badge bg-success text-dark">${item.currentScore}</span>
                            </li>
                        `;
                    list.append(html);
                });
            }

            $('#leaderboard-loader').removeClass('d-flex').removeClass('justify-content-center').removeClass('align-items-center');
            $('#leaderboard-loader').addClass('d-none');
            $('#leaderboard-list').removeClass('d-none');
        } catch (error) {
            console.error('Error loading leaderboard:', error);
            $('#leaderboard-loader').html('<p class="text-danger">Failed to load leaderboard</p>');
        }
    }
</script>
@endcanAccess

@canAccess('dashboardReport','homes')
<script>
    $(document).ready(async function() {
        try {
            $('#loading').show(); // Assume there's an element with id 'loading' for showing the loading state
            const response = await $.ajax({
                url: "{{ route('home.dashboardReport') }}",
                type: "GET",
                dataType: "json",
                beforeSend: function() {
                    $("#totalTasksComplete, #checkin_point_percentage, #totalPoints, #currentScore")
                        .html('<span class="placeholder col-8 placeholder-glow"></span>');
                }
            });

            if (response.status === "success") {
                $('#currentScore').text(response.data.currentScore);
                $('#totalPoints').text(response.data.totalPoints);
                $('#totalTasksComplete').text(response.data.totalTasksComplete);
                $('#checkin_point_percentage').text(response.data.checkin_point_percentage);
                $('#checkins').text(response.data.checkins ? "Sudah" : "Belum");
            }
        } catch (error) {
            console.error('Error fetching dashboard report:', error);
        } finally {
            $('#loading').hide(); // Hide the loading state after the request completes
        }
    });
</script>
@endcanAccess

<script>
    $(document).ready(function() {
        $('input[name="start_date"]').on('change', function() {
            var startDateValue = $(this).val();
            $('input[name="end_date"]').val(startDateValue);
        });
    });
</script>
@canAccess('showScheduleOb','homes')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: [
            @foreach($schedules as $schedule) {
                title: '{{ $schedule->user->name }} - {{ $schedule->shiftingOb->name }}',
                start: '{{ $schedule->date }}',
                description: `
                            <b>User:</b> {{ $schedule->user->name }}<br>
                            <b>Shift:</b> {{ $schedule->shiftingOb->name }}<br>
                            <b>Clock In:</b> {{ $schedule->shiftingOb->clock_in }}<br>
                            <b>Clock Out:</b> {{ $schedule->shiftingOb->clock_out }}<br>
                            <b>Real Clock In:</b> {{ $schedule->attendance ? $schedule->attendance->clock_in : '-' }}<br>
                            <b>Real Clock Out:</b> {{ $schedule->attendance ? $schedule->attendance->clock_out : '-' }}<br>
                        `,
                id: '{{ $schedule->id }}',
                extendedProps: {
                    user_id: '{{ $schedule->user_id }}',
                    shifting_ob_id: '{{ $schedule->shifting_ob_id }}'
                }
            },
            @endforeach
        ],
        eventDidMount: function(info) {
            new bootstrap.Tooltip(info.el, {
                title: info.event.extendedProps.description,
                html: true,
                container: 'body'
            });
        }
    });

    calendar.render();
});
</script>
@endcanAccess

@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.2/main.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
<style>
.hover-card {
    transition: transform 0.3s ease, background-color 0.3s ease;
}

.hover-card:hover {
    background-color: #ffcf63 !important;
    /* Warna hover */
    transform: scale(1.05);
    /* Efek zoom */
}

.hover-card .card-title,
.hover-card .card-text {
    transition: color 0.3s ease;
}

.hover-card:hover .card-title,
.hover-card:hover .card-text {
    color: #000000;
    /* Warna teks saat hover */
}
</style>
<style>
/* Custom Styles */
.hover-effect:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.icon-container {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-soft {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-success-soft {
    background-color: rgba(25, 135, 84, 0.1);
}

.bg-info-soft {
    background-color: rgba(13, 202, 240, 0.1);
}

.bg-danger-soft {
    background-color: rgba(220, 53, 69, 0.1);
}

.btn-hover-effect {
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.btn-hover-effect:hover {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-moon {
    background: linear-gradient(45deg, #2b5876, #4e4376);
}

.bg-fire {
    background: linear-gradient(45deg, #ff416c, #ff4b2b);
}

.bg-purple {
    background: linear-gradient(45deg, #4776e6, #8e54e9);
}
</style>



<style>
/* Custom Styles */
.hover-effect {
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.hover-effect:hover {
    transform: translateY(-5px);
    border-bottom-color: #ffc107;
}

.text-gradient {
    background: linear-gradient(45deg, #2b5876, #4e4376);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.bg-soft-warning {
    background-color: rgba(255, 193, 7, 0.15);
}

.bg-soft-success {
    background-color: rgba(25, 135, 84, 0.08);
}

.text-gradient-gold {
    background: linear-gradient(45deg, #FFD700, #D4AF37);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.avatar-wrapper {
    position: relative;
    display: inline-block;
}

.avatar-wrapper::after {
    content: "";
    position: absolute;
    inset: -5px;
    background: linear-gradient(45deg, #ff6b6b, #ffd93d);
    border-radius: 50%;
    z-index: -1;
    opacity: 0.3;
}
</style>
@endsection