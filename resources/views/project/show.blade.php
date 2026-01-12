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

<!-- Daily Tasks Section -->
<div class="card mt-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-tasks"></i> Daily Tasks
        </h4>
        <span class="badge badge-light ml-auto">{{ $dailyTasks->count() }} Tasks</span>
    </div>
    <div class="card-body">
        @if($dailyTasks->count() > 0)
            <div class="row">
                @foreach($dailyTasks as $task)
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow-sm border-left-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title text-primary">
                                    {{ $task->name_show }}
                                </h5>
                                @if($task->taskStatus)
                                    @php
                                        $statusClass = 'secondary';
                                        $statusIcon = 'circle';
                                        switch($task->taskStatus->name) {
                                            case 'DONE':
                                            case 'COMPLATE':
                                                $statusClass = 'success';
                                                $statusIcon = 'check-circle';
                                                break;
                                            case 'ON PROGRESS':
                                                $statusClass = 'primary';
                                                $statusIcon = 'sync-alt';
                                                break;
                                            case 'PENDING':
                                                $statusClass = 'warning';
                                                $statusIcon = 'clock';
                                                break;
                                            case 'CANCEL':
                                                $statusClass = 'danger';
                                                $statusIcon = 'times-circle';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }}">
                                         @switch($task->taskStatus->name)
                                            @case('backlog')
                                                <i class="fa fa-clipboard-list"></i> Backlog
                                                @break
                                            @case('todo')
                                                <i class="fa fa-list-alt"></i> Todo
                                                @break
                                            @case('doing')
                                                <i class="fa fa-hourglass-start"></i> Doing
                                                @break
                                            @case('in review')
                                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                                @break
                                            @case('not complete')
                                                <i class="fa fa-times-circle" style="color: red;"></i> Not Complete
                                                @break
                                            @case('complete')
                                                <i class="fa fa-check" style="color: green;"></i> Complete
                                                @break
                                            @default    
                                                {{ $dailytask->taskStatus->name }}
                                        @endswitch
                                    </span>
                                @endif
                            </div>
                            
                            <div class="card-text mb-3">
                                <p class="text-muted mb-2" style="max-height: 60px; overflow: hidden;">
                                    {{ Str::limit(strip_tags($task->description), 150) }}
                                </p>
                            </div>
                            
                            <hr class="my-2">
                            
                            <div class="task-meta">
                                <div class="row small text-muted mb-2">
                                    <div class="col-6">
                                        <i class="far fa-calendar-alt"></i>
                                        <strong>Tanggal:</strong><br>
                                        {{ $task->dateShow }}
                                    </div>
                                    <div class="col-6">
                                        @if($task->assign)
                                            <i class="fas fa-user"></i>
                                            <strong>Assigned to:</strong><br>
                                            {{ $task->assign->name }}
                                        @else
                                            <i class="fas fa-user-slash"></i>
                                            <strong>Unassigned</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @canAccess('show','dailytasks')
                            <div class="mt-3">
                                <a href="{{ route('dailytask.show', $task->slug) }}" 
                                   class="btn btn-info btn-sm btn-block">
                                    <i class="fas fa-eye"></i> Lihat Detail Task
                                </a>
                            </div>
                            @endcanAccess
                        </div>
                    </div>
                </div>
                @endforeach
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
    
    /* Daily Tasks Styling */
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    .badge {
        padding: 0.4em 0.6em;
        font-size: 0.85rem;
    }
    
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .card-header h4 {
        margin-bottom: 0;
    }
    
    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transition: box-shadow 0.3s ease-in-out;
    }
    
    .task-meta {
        font-size: 0.875rem;
    }
    
    .h-100 {
        height: 100% !important;
    }

</style>
@endsection
