@extends('adminlte::page')

@section('title', 'Show Project')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('project.index') }}">Daftar Proyek</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$projectEdit->title}}</li>
    </ol>
</nav>

<div class="card p-3 mb-2 mt-3">
    <div class="card-body">
        <h2>Detail Proyek</h2>
        <div class="col-md-12">
            @if(Session::get('deletePurchase'))
                <div class="alert alert-success mt-3">Berhasil Menghapus Pembelian</div>
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

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Nama Proyek</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->title ?? '' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Surat Perintah Kerja</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->workOrder->number_result ?? '' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Jangka Waktu Pekerjaan</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($projectEdit->start_date)->format('d-m-Y') ?? '' }} hingga {{ \Carbon\Carbon::parse($projectEdit->end_date)->format('d-m-Y')?? '' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Keterangan Proyek</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->description ?? '' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Recurring Proyek</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->recurring ? 'Yes' : 'No' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Aktifkan Peringatan</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->alert_expired ? 'Yes' : 'No' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Dailytask Project</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->dailyTaskProjects->first() ? $projectEdit->dailyTaskProjects->first()->name : 'Belum Terdaftar' }}</p>
            </div>
        </div>

        @if($projectEdit->alert_expired)
        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Peringatan</label>
            <div class="col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="expired" id="expired" disabled {{ $projectEdit->alert_expired ? 'checked' : '' }}>
                    <label class="form-check-label" for="expired">Expired</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="one_week" id="oneWeek" disabled {{ $projectEdit->alert_one_week ? 'checked' : '' }}>
                    <label class="form-check-label" for="oneWeek">1 Minggu</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="two_week" id="twoWeeks" disabled {{ $projectEdit->alert_two_week ? 'checked' : '' }}>
                    <label class="form-check-label" for="twoWeeks">2 Minggu</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="one_month" id="oneMonth" disabled {{ $projectEdit->alert_one_month ? 'checked' : '' }}>
                    <label class="form-check-label" for="oneMonth">1 Bulan</label>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2>Surat Perintah Kerja</h2>
        <div class="col-md-12">
            @if(Session::get('deletePurchase'))
                <div class="alert alert-success mt-3">Berhasil Menghapus Pembelian</div>
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

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="mt-5">No SPK: {{ $workOrder->number_result }}</div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label for="date" class="col-sm-4 col-form-label text-right">Tanggal:</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext">{{ $workOrder->date }}</p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-right">Finance:</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext">{{ $workOrder->userCreate->name ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group row mb-3 mt-3">
            <label class="col-sm-2 col-form-label">Pilih No. Quote</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $workOrder->quote->number_result }}</p>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label class="col-sm-2 col-form-label">Customer</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $workOrder->quote->customer->name }}</p>
            </div>
        </div>

        <table class="table table-bordered" id="tableWorkOrder">
            <thead>
                <tr>
                    <th class="col-1">No</th>
                    <th class="col-3">Produk / Jasa</th>
                    <th class="col-3">Description</th>
                    <th class="col-2">Qty</th>
                    <th class="col-2">Budget</th>
                </tr>
            </thead>
            <tbody>
                @php $nomorBaris = 1; @endphp
                @foreach($workOrder->workOrderProduct->sortBy('sort') as $a)
                <tr>
                    <td class="col-1">{{ $nomorBaris++ }}</td>
                    <td class="col-3">{{ $a->product->name ?? "" }}</td>
                    <td class="col-3">{!! $a->description !!}</td>
                    <td class="col-2">{{ $a->qty }}</td>
                    <td class="col-2">{{ 'Rp. '.number_format($a->sub_total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mt-3">
            <div class="col-2 offset-10">
                <div class="d-flex justify-content-between mb-2">
                    <div>Total:</div>
                    <div id="sub_total_result">{{ 'Rp '.number_format($workOrder->total, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="fileUpload">Mohon Upload Quote yang sudah di tanda-tangani:</label>
            @if($workOrder->quote_file)
                <div class="mb-2">
                    <a href="{{ s3_asset(true,10,$workOrder->quote_file) }}" class="btn btn-sm btn-primary" download><i class="fa fa-file-pdf"></i> Download</a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Report Project Section -->
<div class="card mt-3">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-file-alt"></i> Laporan Proyek
        </h4>
        @if($projectEdit->reportProject)
            <span class="badge badge-light ml-auto">1 Report</span>
        @else
            <span class="badge badge-warning ml-auto">Belum Ada</span>
        @endif
    </div>
    <div class="card-body">
        @if($projectEdit->reportProject)
            @php
                $report = $projectEdit->reportProject;
                $totalReports = $report->reportProjectDetail->count();
                $completedReports = $report->reportProjectDetail->where('is_report', 1)->count();
                $completionPercentage = $totalReports > 0 ? round(($completedReports / $totalReports) * 100) : 0;
            @endphp
            
            <div class="row">
                <!-- Report Summary Card -->
                <div class="col-md-6 mb-3">
                    <div class="info-card border-left-success">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="text-muted mb-1">
                                    <i class="fas fa-hashtag"></i> Nomor Laporan
                                </h6>
                                <h4 class="font-weight-bold text-success mb-0">
                                    {{ $report->number ?? '-' }}
                                </h4>
                            </div>
                            <div class="text-right">
                                <h6 class="text-muted mb-1">
                                    <i class="far fa-calendar"></i> Tanggal
                                </h6>
                                <p class="mb-0 font-weight-bold">
                                    {{ $report->date ? \Carbon\Carbon::parse($report->date)->format('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">
                                <i class="fas fa-user-shield"></i> Project Manager
                            </h6>
                            <p class="mb-0 font-weight-bold">
                                {{ $report->userCreate->name ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status & Progress Card -->
                <div class="col-md-6 mb-3">
                    <div class="info-card border-left-info">
                        <!-- Approval Status -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-clipboard-check"></i> Status Persetujuan
                            </h6>
                            @if(isset($report->is_approve))
                                @if($report->is_approve)
                                    <span class="badge badge-success badge-lg px-3 py-2">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                @else
                                    <span class="badge badge-danger badge-lg px-3 py-2">
                                        <i class="fas fa-times-circle"></i> Rejected
                                    </span>
                                    @if($report->note)
                                        <p class="text-danger small mt-2 mb-0">
                                            <strong>Catatan:</strong> {{ Str::limit($report->note, 100) }}
                                        </p>
                                    @endif
                                @endif
                            @else
                                <span class="badge badge-warning badge-lg px-3 py-2">
                                    <i class="fas fa-clock"></i> Menunggu Persetujuan
                                </span>
                            @endif
                        </div>

                        <!-- Progress -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-muted mb-0">
                                    <i class="fas fa-tasks"></i> Progress Laporan
                                </h6>
                                <span class="font-weight-bold">{{ $completedReports }}/{{ $totalReports }}</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: {{ $completionPercentage }}%;" 
                                     aria-valuenow="{{ $completionPercentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $completionPercentage }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Items Summary -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-list-ul"></i> Ringkasan Item Laporan
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="60%">Nama Laporan</th>
                                            <th width="15%" class="text-center">Status</th>
                                            <th width="20%" class="text-center">File/Link</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report->reportProjectDetail->take(5) as $index => $detail)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $detail->name }}</td>
                                            <td class="text-center">
                                                @if($detail->is_report)
                                                    <span class="badge badge-success badge-sm">
                                                        <i class="fa fa-check"></i> Selesai
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary badge-sm">
                                                        <i class="fa fa-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($detail->file || $detail->link)
                                                    <a href="{{ s3_asset(true,10,'reports/' . $a->file) }}" class="btn btn-sm btn-primary" download title="{{ $a->file }}"><i class="fa fa-download"></i></a>
                                                @else
                                                    <i class="fas fa-times-circle text-muted"></i> -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        @if($report->reportProjectDetail->count() > 5)
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                <em>Dan {{ $report->reportProjectDetail->count() - 5 }} item lainnya...</em>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="text-center mt-3">
                <a href="{{ route('report-project.show', $report->slug) }}" 
                   class="btn btn-success btn-sm">
                    <i class="fas fa-eye"></i> Lihat Detail Lengkap Laporan
                </a>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-file-excel fa-5x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">Belum Ada Laporan Proyek</h4>
                    <p class="text-muted mb-4">
                        Laporan proyek untuk "{{ $projectEdit->title }}" belum dibuat.<br>
                        Silakan buat laporan proyek untuk memulai dokumentasi.
                    </p>
                    @canAccess('create', 'report_projects')
                    <a href="{{ route('report-project.create', ['project' => $projectEdit->id]) }}" 
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Buat Laporan Proyek
                    </a>
                    @endcanAccess
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Daily Tasks Section -->
<div class="card mt-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-tasks"></i> Daily Tasks
        </h4>
        <span class="badge badge-light ml-auto">{{ $dailyTasks->count() }} Tasks</span>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filterStatus">Filter by Status:</label>
                <select id="filterStatus" class="form-control">
                    <option value="">-- Semua Status --</option>
                    <option value="backlog">Backlog</option>
                    <option value="todo">Todo</option>
                    <option value="doing">Doing</option>
                    <option value="in review">In Review</option>
                    <option value="not complete">Not Complete</option>
                    <option value="complete">Complete</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchTask">Search Task:</label>
                <input type="text" id="searchTask" class="form-control" placeholder="Cari nama task...">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button id="resetFilter" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset Filter
                </button>
            </div>
        </div>

        @if($dailyTasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="dailyTasksTable">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="20%">Nama Task</th>
                            <th width="30%">Deskripsi</th>
                            <th width="12%">Tanggal</th>
                            <th width="15%">Assigned To</th>
                            <th width="13%" class="text-center">Status</th>
                            <th width="5%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyTasks as $index => $task)
                        <tr data-status="{{ strtolower($task->taskStatus->name ?? '') }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><strong>{{ $task->name_show }}</strong></td>
                            <td class="text-muted">
                                {{ Str::limit(strip_tags($task->description), 100) }}
                            </td>
                            <td>
                                <i class="far fa-calendar-alt text-primary"></i>
                                {{ $task->dateShow }}
                            </td>
                            <td>
                                @if($task->assign)
                                    <i class="fas fa-user text-success"></i>
                                    {{ $task->assign->name }}
                                @else
                                    <i class="fas fa-user-slash text-muted"></i>
                                    <em class="text-muted">Unassigned</em>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($task->taskStatus)
                                    @php
                                        $statusClass = 'secondary';
                                        $statusIcon = 'circle';
                                        $statusName = strtolower($task->taskStatus->name);
                                        
                                        switch($statusName) {
                                            case 'complete':
                                                $statusClass = 'success';
                                                $statusIcon = 'check';
                                                break;
                                            case 'doing':
                                                $statusClass = 'primary';
                                                $statusIcon = 'hourglass-start';
                                                break;
                                            case 'todo':
                                                $statusClass = 'info';
                                                $statusIcon = 'list-alt';
                                                break;
                                            case 'in review':
                                                $statusClass = 'warning';
                                                $statusIcon = 'eye';
                                                break;
                                            case 'not complete':
                                                $statusClass = 'danger';
                                                $statusIcon = 'times-circle';
                                                break;
                                            case 'backlog':
                                                $statusClass = 'secondary';
                                                $statusIcon = 'clipboard-list';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }} badge-pill px-3 py-2">
                                        <i class="fa fa-{{ $statusIcon }}"></i>
                                        {{ ucfirst($task->taskStatus->name) }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary badge-pill px-3 py-2">
                                        <i class="fa fa-question-circle"></i> Unknown
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @canAccess('show','dailytasks')
                                <a href="{{ route('dailytask.show', $task->slug) }}" 
                                   class="btn btn-info btn-sm" 
                                   title="Lihat Detail"
                                   data-toggle="tooltip">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcanAccess
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Task Statistics -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Statistik Tasks</h5>
                            <div class="row text-center">
                                @php
                                    $statusCounts = $dailyTasks->groupBy(function($task) {
                                        return strtolower($task->taskStatus->name ?? 'unknown');
                                    })->map->count();
                                @endphp
                                
                                <div class="col-md-2">
                                    <div class="stat-item">
                                        <h3 class="text-secondary">{{ $statusCounts->get('backlog', 0) }}</h3>
                                        <p class="text-muted mb-0">Backlog</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stat-item">
                                        <h3 class="text-info">{{ $statusCounts->get('todo', 0) }}</h3>
                                        <p class="text-muted mb-0">Todo</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stat-item">
                                        <h3 class="text-primary">{{ $statusCounts->get('doing', 0) }}</h3>
                                        <p class="text-muted mb-0">Doing</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stat-item">
                                        <h3 class="text-warning">{{ $statusCounts->get('in review', 0) }}</h3>
                                        <p class="text-muted mb-0">In Review</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stat-item">
                                        <h3 class="text-success">{{ $statusCounts->get('complete', 0) }}</h3>
                                        <p class="text-muted mb-0">Complete</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stat-item">
                                        <h3 class="text-danger">{{ $statusCounts->get('not complete', 0) }}</h3>
                                        <p class="text-muted mb-0">Not Complete</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center mb-0">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p class="mb-0">Tidak ada daily task untuk proyek ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('css')
<style>
    .form-control-plaintext {
        background-color: #f9f9f9;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    
    /* Daily Tasks Table Styling */
    #dailyTasksTable {
        font-size: 0.9rem;
    }
    
    #dailyTasksTable thead th {
        background-color: #343a40;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    #dailyTasksTable tbody tr {
        transition: all 0.3s ease;
    }
    
    #dailyTasksTable tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .badge-pill {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .stat-item {
        padding: 15px;
        border-radius: 5px;
        background: white;
        margin: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-item h3 {
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .table-responsive {
        border-radius: 5px;
        overflow: hidden;
    }
    
    /* Filter styling */
    #filterStatus, #searchTask {
        border-radius: 5px;
        border: 1px solid #ced4da;
    }
    
    #filterStatus:focus, #searchTask:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    /* Report Project Styling */
    .info-card {
        padding: 20px;
        border-radius: 8px;
        background: #f8f9fa;
        border-left: 4px solid #28a745;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        height: 100%;
    }

    .border-left-success {
        border-left-color: #28a745 !important;
    }

    .border-left-info {
        border-left-color: #17a2b8 !important;
    }

    .badge-lg {
        padding: 0.5em 1em;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .badge-sm {
        padding: 0.3em 0.6em;
        font-size: 0.8rem;
    }

    .progress {
        border-radius: 10px;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    }

    .progress-bar {
        border-radius: 10px;
        font-weight: bold;
        font-size: 0.9rem;
        line-height: 25px;
    }

    /* Empty State */
    .empty-state {
        padding: 30px;
        animation: fadeIn 0.5s ease-in;
    }

    .empty-state i {
        opacity: 0.3;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Card Hover Effect */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }

    /* Table Styling in Report Summary */
    .table-sm th {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-sm td {
        font-size: 0.9rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,0.05);
        cursor: pointer;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .info-card {
            margin-bottom: 15px;
        }
        
        .badge-lg {
            font-size: 0.85rem;
            padding: 0.4em 0.8em;
        }
        
        .empty-state i {
            font-size: 3rem !important;
        }
    }
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    console.log('Daily Tasks Script Loaded'); // Debug log
    
    // Filter by status
    $('#filterStatus').on('change', function() {
        console.log('Status filter changed:', $(this).val()); // Debug log
        filterTasks();
    });
    
    // Search task
    $('#searchTask').on('keyup', function() {
        console.log('Search text:', $(this).val()); // Debug log
        filterTasks();
    });
    
    // Reset filter
    $('#resetFilter').on('click', function() {
        console.log('Reset clicked'); // Debug log
        $('#filterStatus').val('');
        $('#searchTask').val('');
        filterTasks();
    });
    
    function filterTasks() {
        var statusFilter = $('#filterStatus').val().toLowerCase();
        var searchText = $('#searchTask').val().toLowerCase();
        var visibleCount = 0;
        
        console.log('Filtering - Status:', statusFilter, 'Search:', searchText); // Debug log
        
        // Remove existing no-results row first
        $('#dailyTasksTable tbody tr.no-results').remove();
        
        $('#dailyTasksTable tbody tr:not(.no-results)').each(function() {
            var $row = $(this);
            var rowStatus = $row.attr('data-status');
            var rowText = $row.text().toLowerCase();
            
            var statusMatch = statusFilter === '' || rowStatus === statusFilter;
            var searchMatch = searchText === '' || rowText.indexOf(searchText) > -1;
            
            console.log('Row status:', rowStatus, 'Match:', statusMatch && searchMatch); // Debug log
            
            if (statusMatch && searchMatch) {
                $row.show();
                visibleCount++;
                // Update nomor urut
                $row.find('td:first-child').text(visibleCount);
            } else {
                $row.hide();
            }
        });
        
        console.log('Visible count:', visibleCount); // Debug log
        
        // Show message if no results
        if (visibleCount === 0) {
            $('#dailyTasksTable tbody').append(
                '<tr class="no-results">' +
                    '<td colspan="7" class="text-center text-muted py-4">' +
                        '<i class="fas fa-search fa-2x mb-2 d-block"></i>' +
                        'Tidak ada task yang sesuai dengan filter' +
                    '</td>' +
                '</tr>'
            );
        }
    }
});
</script>
@endsection