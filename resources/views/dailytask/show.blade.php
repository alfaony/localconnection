@extends('adminlte::page')

@php
    $startDate = \Carbon\Carbon::parse($dailytask->start_date);
    $endDate = \Carbon\Carbon::parse($dailytask->end_date);
    $isOverdue = $dailytask->isOverdue();
@endphp

@section('content')

        <!-- Alert Messages -->
        @foreach (['report', 'deletemedia', 'updatemedia', 'approvement', 'extend', 'comment', 'Subtask','Working'] as $msg)
            @if(Session::get($msg))
                <div class="alert alert-success mt-3">{{ ucfirst($msg) }} Berhasil</div>
            @endif
        @endforeach
        @if(Session::get('delete'))
        <div class="alert alert-success mt-3">Tugas Berhasil Terhapus</div>
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

        <!-- Card for Task Details -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dailytask.index') }}">Tugas Harian</a></li>
                @if($dailytask->head)
                    <li class="breadcrumb-item"><a href="{{ route('dailytask.show', $dailytask->head->slug) }}">{{ $dailytask->head->name }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $dailytask->nameShow ?? '' }}</li>
            </ol>
        </nav>

        <div class="card p-3 mt-3 shadow-sm">
            <div class="card-body row">
                <!-- Left Column -->
                <div class="col-md-6 mb-3 mb-md-0 mr-md-3">
                    <div class="form-group row">
                        <label for="name" class="col-sm-4 col-form-label">Main Proyek:</label>
                        <div class="col-sm-8">
                            @if($showProject)
                            <p class="form-control-plaintext">
                                @if($dailytask->project)
                                <a href="{{ route('daily_task_project.showproject', $dailytask->project->slug) }}" class="btn btn-info badge badge-pill btn-sm badge-md">{{ $dailytask->project->name ?? "" }}</a>
                                @endif
                            </p>
                            @else 
                            <p class="form-control-plaintext">
                                @if($dailytask->project)
                                {{ $dailytask->project->name ?? "" }}
                                @endif
                            </p> 
                            @endif
                        </div>
                    </div>

                    @if($dailytask->dataProject)
                    <div class="form-group row">
                        <label for="name" class="col-sm-4 col-form-label">Data Proyek:</label>
                        <div class="col-sm-8">

                            <p class="form-control-plaintext">
                                {{ $dailytask->dataProject->title ?? "" }}
                            </p> 
                        </div>
                    </div>
                    @endif

                    <div class="form-group row">
                        <label for="name" class="col-sm-4 col-form-label">Tugas:</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $dailytask->name }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="name" class="col-sm-4 col-form-label">Ditugaskan:</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext"><span class="badge badge badge-pill badge-info">{{ $dailytask->assign->name ?? '' }}</span></p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="start_date" class="col-sm-4 col-form-label">Tanggal:</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext {{ $isOverdue ? 'text-danger' : '' }}">
                                {{ $startDate->format('d-m-Y') }} - {{ $endDate->format('d-m-Y') }}
                            </p>
                        </div>
                    </div>

                    @if($dailytask->submit)
                        <div class="form-group row">
                            <label for="submit_date" class="col-sm-4 col-form-label">Tanggal Submit:</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext {{ $dailytask->status_submit == 'late' ? 'text-danger' : 'text-success' }}">
                                    {{ \Carbon\Carbon::parse($dailytask->submit)->format('d-m-Y') }} 
                                    {{ $dailytask->date_range_submit }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($dailytask->status_submit)
                        <div class="form-group row">
                            <label for="status_submit" class="col-sm-4 col-form-label">Status Submit:</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext {{ $dailytask->status_submit == 'late' ? 'text-danger' : 'text-success' }}">
                                    {{ ucfirst($dailytask->status_submit) }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="form-group row">
                        <label for="status" class="col-sm-4 col-form-label">Status Tugas:</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @switch($dailytask->taskStatus->name)
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
                            </p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="name" class="col-sm-4 col-form-label">Kategori:</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $dailytask->category->name ?? "" }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="name" class="col-sm-4 col-form-label">Tipe:</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $dailytask->type->name ?? "" }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="description" class="col-sm-4 col-form-label">Deskripsi:</label>
                        <div class="col-sm-8" style="max-height: 40vh; overflow-y: auto;">
                            <p class="form-control-plaintext">{!! $dailytask->description !!}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="media">Media:</label>
                        @if($dailytask->user_id == Auth::user()->id)
                            @canAccess('updatemedia','dailytasks')
                            <button class="btn btn-success mb-3 btn-sm" data-toggle="modal" data-target="#uploadModalAttachForm">
                                <i class="fa fa-plus"></i> File
                            </button>
                            @endcanAccess
                        @endif
                        @if($dailytask->taskMedia->count())
                        <div class="row" style="max-height: 200px; overflow-y: auto;">
                            @foreach($dailytask->taskMedia as $media)
                                <div class="card mr-2">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            @if(strpos($media->file_type, 'image') !== false)
                                                <i class="fa fa-file-image-o"></i> {{ Str::limit(basename($media->file_path), 15) }}
                                            @elseif(strpos($media->file_type, 'pdf') !== false)
                                                <i class="fa fa-file-pdf-o"></i> {{ Str::limit(basename($media->file_path), 15) }}
                                            @elseif(strpos($media->file_type, 'msword') !== false || strpos($media->file_type, 'officedocument.wordprocessingml.document') !== false)
                                                <i class="fa fa-file-word-o"></i> {{ Str::limit(basename($media->file_path), 15) }}
                                            @else
                                                <i class="fa fa-file"></i> {{ Str::limit(basename($media->file_path), 15) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $media->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $media->id }}">
                                                    <a class="dropdown-item" href="{{ asset('storage/' . $media->file_path) }}" target="_blank">
                                                        <i class="fa fa-download"></i> Download
                                                    </a>
                                                    @canAccess('deletemedia','dailytasks')
                                                    <form action="{{ route('dailytask.deletemedia', $media->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                    @endcanAccess
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    @if($dailytask->customFieldValues && count($dailytask->customFieldValues) > 0)
                    <div class="accordion" id="customFieldAccordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        Detail Project
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#customFieldAccordion">
                                <div class="card-body">
                                    @php
                                        $customFields = [];
                                    @endphp
                                    @foreach($dailytask->customFieldValues as $custom)
                                        @php
                                            $customField = $custom->customField;
                                            $customFieldValue = $custom->customFieldValue;
                                            $fieldName = $customField->name ?? '';
                                            $fieldValue = $customFieldValue->value ?? '';
                                            if ($customField->type === \App\Schemas\ParamSchema::MULTISELECT) {
                                                if (!isset($customFields[$fieldName])) {
                                                    $customFields[$fieldName] = [];
                                                }
                                                $customFields[$fieldName][] = $fieldValue;
                                            } else {
                                                $customFields[$fieldName] = $fieldValue;
                                            }
                                        @endphp
                                    @endforeach
                                    @if($customFields)
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                                @foreach($customFields as $fieldName => $fieldValue)
                                                <tr>
                                                    <td><strong>{{ $fieldName }}</strong></td>
                                                    <td>
                                                        @if(is_array($fieldValue))
                                                            @foreach($fieldValue as $value)
                                                                <span class="d-block badge badge-info badge badge-pill badge-sm m-1">{{ $value }}</span>
                                                            @endforeach
                                                        @else
                                                            {{ $fieldValue }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- Accordion for Objective and Key Results -->
                    <div class="accordion mt-4" id="objectiveAccordion">
                        <div class="card">
                            <div class="card-header" id="headingObjectives">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseObjectives" aria-expanded="true" aria-controls="collapseObjectives">
                                        Objective Dan Key Result
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseObjectives" class="collapse" aria-labelledby="headingObjectives" data-parent="#objectiveAccordion">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="objective_name">Objective:</label>
                                        <span class="d-block badge badge-info badge badge-pill badge-sm m-1">{{ $dailytask->objective ? $dailytask->objective->name : '' }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <label for="objective_name">Key Result:</label>
                                    @foreach($dailytask->keyResults as $keyResult)
                                    <div class="form-group">
                                        <span class="d-block badge badge-info badge badge-pill badge-sm m-1">{{ $keyResult->result }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                                            
                    @if($dailytask->taskStatus->name == \App\Schemas\ParamSchema::DOING)
                        @canAccess('extend','dailytasks')
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#extendTaskModal">
                                <i class="fa fa-book"></i> Perpanjang Tugas
                            </button>
                        @endcanAccess
                    @endif

                </div>

                <!-- Right Column -->
                <div class="col-md-5">
                    @if($dailytask->taskStatus->name == \App\Schemas\ParamSchema::TODO || $dailytask->taskStatus->name == \App\Schemas\ParamSchema::NOTCOMPLATE )                    
                        <h6>Tugas</h6>
                        <form action="{{ route('dailytask.statuschange',$dailytask->slug) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="task_status" value="{{ $doing->id }}"> <!-- Replace 1 with the actual task ID -->
                            <button type="submit" class="btn btn-success">Mulai Pekerjaan</button>
                        </form>
                    @endif
                    
                    @if($dailytask->taskStatus->name == \App\Schemas\ParamSchema::DOING)
                        @canAccess('report','dailytasks')
                            <h6>Laporan Tugas</h6>
                            <form action="{{ route('dailytask.report', $dailytask->slug) }}" method="POST" enctype="multipart/form-data" id="reportForm">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="note">Catatan</label>
                                    <input class="thriveEditor form-control" id="description_note" data-ids="note" name="note" placeholder="yang akan dicetak di perjanjian"/>
                                </div>
                                <div class="form-group">
                                    <label for="media">Upload</label>
                                    <input type="file" id="mediaReport" name="media[]" class="form-control" multiple>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Laporan</button>
                            </form>
                        @endcanAccess
                    @endif

                    @if($dailytask->taskStatus->name == \App\Schemas\ParamSchema::INREVIEW)
                        <h6>Laporan Catatan dan Media</h6>
                        @if($dailytask->report_note)
                            <div class="form-group">
                                <label for="notes">Catatan:</label>
                                {!! $dailytask->report_note !!}
                            </div>
                        @endif

                        @if($dailytask->media->count())
                            <div class="form-group">
                                <label for="media">Media:</label>
                                <div class="row" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($dailytask->media as $media)
                                        <div class="card mr-2">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if(strpos($media->file_type, 'image') !== false)
                                                        <i class="fa fa-file-image-o"></i> {{ Str::limit(basename($media->file_path), 15) }}
                                                    @elseif(strpos($media->file_type, 'pdf') !== false)
                                                        <i class="fa fa-file-pdf-o"></i> {{ Str::limit(basename($media->file_path), 15) }}
                                                    @elseif(strpos($media->file_type, 'msword') !== false || strpos($media->file_type, 'officedocument.wordprocessingml.document') !== false)
                                                        <i class="fa fa-file-word-o"></i> {{ Str::limit(basename($media->file_path), 15) }}
                                                    @else
                                                        <i class="fa fa-file"></i> {{ Str::limit(basename($media->file_path), 15) }}
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $media->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $media->id }}">
                                                            <a class="dropdown-item" href="{{ asset('storage/' . $media->file_path) }}" target="_blank">
                                                                <i class="fa fa-download"></i> Download
                                                            </a>
                                                            @canAccess('deletemedia','dailytasks')
                                                            <form action="{{ route('dailytask.deletemedia', $media->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fa fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                            @endcanAccess
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($dailytask->taskStatus->name != \App\Schemas\ParamSchema::COMPLATE)
                            @canAccess('updatemedia','dailytasks')
                            <button class="btn btn-success mb-3" data-toggle="modal" data-target="#uploadModal">
                                <i class="fa fa-upload"></i> Upload Lampiran
                            </button>
                            @endcanAccess
                        @endif
                    @endif

                    @if($dailytask->taskStatus->name == \App\Schemas\ParamSchema::INREVIEW)
                        @canAccess('approvement','dailytasks')
                        <h6>Penilaian dan Penyelesaian</h6>
                        <form action="{{ route('dailytask.approvement', $dailytask->slug) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="point">Status Tugas</label>
                                <select name="task_status" id="" class="form-control select2" required>
                                    @foreach($approvement as $a)
                                        <option value="{{ $a->id }}">{{ ucfirst($a->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="point">Poin</label>
                                <input type="number" name="point" class="form-control" value="{{ $dailytask->point }}">
                            </div>
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure?')">Simpan Tugas</button>
                        </form>
                        @endcanAccess
                    @elseif($dailytask->taskStatus->name == \App\Schemas\ParamSchema::COMPLATE)
                        <h6>Informasi Pekerjaan</h6>
                        <div class="alert alert-info">
                            <i class="fa fa-check-circle"></i> Pekerjaan telah diselesaikan.
                        </div>
                        <div class="form-group">
                            <label for="point">Poin yang Diberikan</label>
                            <input type="number" name="point" class="form-control" value="{{ $dailytask->point }}" readonly>
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-start mt-4">
                @if($dailytask->user_id == Auth::user()->id)
                @canAccess('edit','dailytasks')
                <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-info"><i class="fa fa-edit"></i> Edit</a>
                @endif
                @endcanAccess
                <a href="{{ route('dailytask.index') }}" class="btn btn-secondary ml-2"><i class="fa fa-arrow-left"></i> Kembali</a>
            </div>
        </div>

        <div class="row">
        <!-- Sub Tugas -->

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Sub Tugas</h6>
                </div>
                <div class="card-body">
                    <!-- Form for Adding Sub Task -->
                    @canAccess('storesubtask','dailytasks')
                    <form action="{{ route('dailytask.storesubtask', $dailytask->slug) }}" method="POST" id="subForm">
                        @csrf
                        @method('PUT')
                        <!-- Row for Dates -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="end_date">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <!-- Row for Task Name and Users -->
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="name">Nama Sub Tugas</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="user_id">Ditugaskan</label>
                                <select class="form-control select2" id="user_id" name="user_id" required>
                                    <option value="">Pilih User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="custom-fields-container"></div>
                        <!-- Row for Description -->
                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <input type="text" name="description_subtask" class="form-control thriveEditor" id="description_description_subtask" data-ids="description_subtask">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Sub Tugas</button>
                    </form>

                    <!-- Existing Sub Tasks -->
                    <ul class="list-group mt-3" id="existing-ttasks-list">
                        @foreach($subTasks as $subTask)
                            @php
                                $isOverdueSub = $subTask->isOverdue();
                            @endphp
                            <li class="list-group-item">
                                <div class="task-details">
                                    <span class="task-name">
                                        <a href="{{ route('dailytask.show', $subTask->slug) }}">{{ Str::limit($subTask->name, 15) }}</a>
                                    </span>
                                    <span class="{{ $isOverdueSub ? 'text-danger' : '' }}">
                                        {{ $subTask->dateShow }}
                                    </span>
                                    <span>
                                        @switch($subTask->taskStatus->name)
                                             @case('todo')
                                            <i class="fa fa-list-alt"></i>
                                            @break
                                            @case('doing')
                                                <i class="fa fa-hourglass-start"></i>
                                                @break
                                            @case('in review')
                                                <i class="fa fa-eye" style="color: green;"></i>
                                                @break
                                            @case('not complete')
                                                <i class="fa fa-times-circle" style="color: red;"></i>
                                                @break
                                            @case('complete')
                                                <i class="fa fa-check" style="color: green;"></i>
                                                @break
                                            @default
                                                {{ $dailytask->taskStatus->name }}
                                        @endswitch
                                    </span>
                                    <span>
                                        {{ $subTask->assign ? $subTask->assign->name : '' }}
                                    </span>
                                </div>
                                <div class="task-actions">
                                    <a href="{{ route('dailytask.edit', $subTask->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                    <form action="{{ route('dailytask.destroy', $subTask->slug) }}" method="POST">
                                        @if($dailytask->user_id == Auth::user()->id)
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return window.confirm('{{ __('Apakah Anda Yakin Hapus Data ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                        @endif
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @endcanAccess
                </div>
            </div>
        </div>

        <!-- Komentar -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Aktifitas</h6>
                </div>
                <div class="card-body">
                    @if($dailytask->taskStatus->name != \App\Schemas\ParamSchema::COMPLATE)
                        @canAccess('comment','dailytasks')
                        <form id="commentForm" action="{{ route('dailytask.comment', $dailytask->slug) }}" method="POST"  enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="comment">Komentar</label>
                                <input type="text" name="message" class="form-control thriveEditor" id="description_message" data-ids="message">
                            </div>
                            <div class="form-group">
                                <label for="comment">Upload File</label>
                                <input type="file" id="mediaComment" name="file_path" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm"> <i class="fa fa-plus"></i> Komentar</button>
                        </form>
                        @endcanAccess
                    @endif
                
                    <!-- Comment List -->
                    <ul class="nav nav-tabs mt-3" id="taskTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="comments-tab" data-toggle="tab" href="#comments" role="tab" aria-controls="comments" aria-selected="true">Komentar Aktifitas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="status-tab" data-toggle="tab" href="#status" role="tab" aria-controls="status" aria-selected="false">Riwayat Timeline</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <!-- Komentar Aktifitas Tab -->
                        <div class="tab-pane fade show active" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                            <h6 class="mt-4">Riwayat Aktifitas</h6>
                            <div style="max-height: 50vh; overflow-y: auto;">
                                @foreach($dailytask->message as $comment)
                                    <div class="media mb-3">
                                        {{-- <img src="{{ $comment->user->profile_image_url ?? 'https://via.placeholder.com/50' }}" class="mr-3" alt="User Image"> --}}
                                        <div class="media-body">
                                            <h6 class="mt-0"> {{ $comment->user ? $comment->user->name : '-' }}</h6>
                                            {!! $comment->message !!}
                                            <small class="text-muted">Posted on: {{ $comment->created_at->format('d-m-Y') }}</small>
                                            @if($comment->file_path)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/' . $comment->file_path) }}" target="_blank" class="btn btn-primary btn-sm">
                                                        <i class="fa fa-download"></i> Download Attachment
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <!-- Riwayat Status Tab -->
                        <div class="tab-pane fade" id="status" role="tabpanel" aria-labelledby="status-tab">
                            <div class="card-body" style="max-height: 50vh; overflow-y: auto;">
                                <div class="timeline">
                                    @foreach ($dailytask->statusRecords as $index => $record)
                                        <div class="timeline-item">
                                            <div class="timeline-icon">
                                                @switch($record->taskStatus->name)
                                                    @case('todo')
                                                        <i class="fa fa-list"></i>
                                                        @break
                                                    @case('doing')
                                                        <i class="fa fa-hourglass-start"></i>
                                                        @break
                                                    @case('in review')
                                                        <i class="fa fa-eye" style="color: green;"></i>
                                                        @break
                                                    @case('not complete')
                                                        <i class="fa fa-times-circle" style="color: red;"></i>
                                                        @break
                                                    @case('complete')
                                                        <i class="fa fa-check" style="color: green;"></i>
                                                        @break
                                                    @default
                                                        <i class="fa fa-info-circle"></i>
                                                @endswitch
                                            </div>
                                            <div class="timeline-content">
                                                <h5>{{ ucfirst($record->taskStatus->name) }}</h5>
                                                <p>
                                                    @if ($index > 0)
                                                        @php
                                                            $previousRecord = $dailytask->statusRecords[$index - 1];
                                                            $timeDiff = $record->created_at->diffForHumans($previousRecord->created_at, true);
                                                        @endphp
                                                        <span> {{ $previousRecord->taskStatus->name }} </span>
                                                        <span class="text-muted">{{ $timeDiff }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('dailytask.updatemedia', $dailytask->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Upload More Files</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="media">Upload Media</label>
                            <input type="file" id="mediaInput" name="media[]" class="form-control" multiple>
                        </div>
                    </div>
                    <input type="hidden" name="status" value="file_report">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModalAttachForm" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('dailytask.updatemedia', $dailytask->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Upload More Files</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="media">Upload Media</label>
                            <input type="file" id="mediaInput" name="media[]" class="form-control" multiple>
                        </div>
                    </div>
                    <input type="hidden" name="status" value="file_task">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Perpanjang Tugas -->
    <div class="modal fade" id="extendTaskModal" tabindex="-1" role="dialog" aria-labelledby="extendTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extendTaskModalLabel">Perpanjang Tugas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('dailytask.extend', $dailytask->slug) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $dailytask->start_date }}" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Tanggal Berakhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $dailytask->end_date }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal untuk Riwayat Komentar -->
    <div class="modal fade" id="commentHistoryModal" tabindex="-1" role="dialog" aria-labelledby="commentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentHistoryModalLabel">Riwayat Komentar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Dummy Comments -->
                    <div class="media mb-3">
                        <img src="https://via.placeholder.com/50" class="mr-3" alt="User Image">
                        <div class="media-body">
                            <h6 class="mt-0">User 1</h6>
                            Komentar pertama pada tugas ini.
                            <small class="text-muted">Posted on: 2024-05-17</small>
                        </div>
                    </div>
                    <div class="media mb-3">
                        <img src="https://via.placeholder.com/50" class="mr-3" alt="User Image">
                        <div class="media-body">
                            <h6 class="mt-0">User 2</h6>
                            Komentar kedua pada tugas ini.
                            <small class="text-muted">Posted on: 2024-05-18</small>
                        </div>
                    </div>
                    <!-- Add more comments as needed -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('dailytask.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="task_name">Tugas</label>
                            <input type="text" class="form-control" id="task_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="task_description">Deskripsi</label>
                            <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" placeholder="yang akan dicetak di perjanjian"/>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Tanggal Berakhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <label for="assignment_user_id">Ditugaskan</label>
                            <select class="form-control select2" id="assignment_user_id" name="assignment_user_id" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="category_id">K</label>
                            <select class="form-control select2" id="category_id" name="category_id" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type_id">Type</label>
                            <select class="form-control select2" id="type_id" name="type_id" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="poin">Poin</label>
                            <input type="number" class="form-control" id="poin" name="poin" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Task</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Form for Adding Comment -->
    {{--
    <div class="card mt-3">
        <div class="card-header">
            <h5>Tambah Komentar</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                @csrf
                <div class="form-group">
                    <label for="comment">Komentar</label>
                    <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="Tambahkan komentar Anda"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Tambahkan Komentar</button>
            </form>
        </div>
    </div>
    --}}
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() 
    {
        $('.select2').select2();
        // Assume you have a dailyTaskId variable available or extract it from the form
        var dailyTaskId = "{{ $dailytask->id }}";
        loadCustomFields(dailyTaskId);
    });
    function loadCustomFields(dailyTaskId = null) 
    {
        var projectId = "{{ $dailytask->daily_task_project_id }}";
        var url = '{{ url('daily_task_project/getcustomfield') }}/' + projectId;
        
        $.ajax({
            url: url,
            type: 'GET',
            data: 
            {
                dailyTaskId: dailyTaskId // Passing dailyTaskId to the server
            },
            success: function(data) {
                $('#custom-fields-container').html(data);
                $('.select2-single, .select2-multiple').select2(); // Re-initialize select2
            }
        });
    }

    $('#mediaReport').on('change', function() 
    {
        var maxFileSize = 1 * 1024 * 1024; // 5MB in bytes
        var files = this.files;
        var validFiles = [];

        for (var i = 0; i < files.length; i++) {
            if (files[i].size > maxFileSize) {
                alert('File ' + files[i].name + ' terlalu besar dan akan dihapus. Batas maksimal 1 Mb');
            } else {
                validFiles.push(files[i]);
            }
        }

        // Clear the input and add back the valid files
        $(this).val('');
        var dataTransfer = new DataTransfer();
        for (var j = 0; j < validFiles.length; j++) {
            dataTransfer.items.add(validFiles[j]);
        }
        this.files = dataTransfer.files;
    });

    $('#mediaComment').on('change', function() 
    {
        var maxFileSize = 1 * 1024 * 1024; // 5MB in bytes
        var files = this.files;
        var validFiles = [];

        for (var i = 0; i < files.length; i++) {
            if (files[i].size > maxFileSize) {
                alert('File ' + files[i].name + ' terlalu besar dan akan dihapus. Batas maksimal 1 Mb');
            } else {
                validFiles.push(files[i]);
            }
        }

        // Clear the input and add back the valid files
        $(this).val('');
        var dataTransfer = new DataTransfer();
        for (var j = 0; j < validFiles.length; j++) {
            dataTransfer.items.add(validFiles[j]);
        }
        this.files = dataTransfer.files;
    });

    $('#mediaInput').on('change', function() 
    {
        var maxFileSize = 1 * 1024 * 1024; // 5MB in bytes
        var files = this.files;
        var validFiles = [];

        for (var i = 0; i < files.length; i++) {
            if (files[i].size > maxFileSize) {
                alert('File ' + files[i].name + ' terlalu besar dan akan dihapus. Batas maksimal 1 Mb');
            } else {
                validFiles.push(files[i]);
            }
        }

        // Clear the input and add back the valid files
        $(this).val('');
        var dataTransfer = new DataTransfer();
        for (var j = 0; j < validFiles.length; j++) {
            dataTransfer.items.add(validFiles[j]);
        }
        this.files = dataTransfer.files;
    });
</script>
<script>
$(document).ready(function() {
    $('#commentForm').on('submit', function(e) {
        var messageContent = $('#description_message').val().trim();
        var messageContentText = $('<div>').html(messageContent).text().trim();

        // Check if the message is empty or only contains empty HTML tags
        if (messageContent === '' || messageContentText === '') {
            e.preventDefault(); // Prevent form submit
            alert('Field komentar wajib diisi!');
        }
    });

    $('#reportForm').on('submit', function(e) {
        var messageContent = $('#description_note').val().trim();
        var messageContentText = $('<div>').html(messageContent).text().trim();

        // Check if the message is empty or only contains empty HTML tags
        if (messageContent === '' || messageContentText === '') {
            e.preventDefault(); // Prevent form submit
            alert('Field komentar wajib diisi!');
        }
    });

    $('.select2').select2({
        width: '100%',
        placeholder: 'Pilih'
    });

    $('input[name="start_date"]').on('change', function() {
        var startDateValue = $(this).val(); // Ambil nilai dari startDate
        $('input[name="end_date"]').val(startDateValue); // Set nilai startDate ke endDate
    });
});
</script>
@endsection

@section('css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .container {
        background-color: #fff;
        border-radius: 5px;
    }
    .media img {
        max-width: 100%;
        height: auto;
    }
    .text-danger {
        color: red;
    }
    .text-success {
        color: green;
    }
    .form-control-plaintext {
        display: inline-block;
        padding-top: .375rem;
        padding-bottom: .375rem;
        margin-bottom: 0;
        line-height: 1.5;
        background-color: transparent;
        border: solid transparent;
        border-width: 1px 0;
    }
    .form-group.row {
        margin-bottom: 1rem;
    }
    .select2-selection__rendered 
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single 
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    .ql-container 
    {
        min-height: 150px;
        height: auto;
    }
</style>
<style>
    .list-group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .task-details {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .task-details span {
        margin-right: 10px;
    }
    .task-actions {
        display: flex;
        align-items: center;
    }
    .task-actions form {
        margin: 0;
    }
    .task-actions a,
    .task-actions button {
        margin-right: 5px;
    }
    .select2-selection__choice
    {
        background-color: #007bff !important;
        border: 1px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 1px solid #007bff !important;
    }
</style>
<style>
    .timeline {
        position: relative;
        padding: 20px 0;
        list-style: none;
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #c5c5c5;
        left: 20px;
        margin-right: -1.5px;
    }
    .timeline-item {
        margin: 0 0 20px;
        padding-left: 40px;
        position: relative;
    }
    .timeline-item:before {
        content: "";
        display: table;
    }
    .timeline-item:after {
        content: "";
        display: table;
        clear: both;
    }
    .timeline-icon {
        position: absolute;
        left: 10px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #f4f4f4;
        border: 0px solid #c5c5c5;
        text-align: center;
        line-height: 12px;
    }
    .timeline-icon i {
        font-size: 12px;
        line-height: 12px;
        margin-top: 4px;
    }
    .timeline-content {
        position: relative;
        padding: 10px 15px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .timeline-content h5 {
        margin-top: 0;
        color: #333;
    }
    .timeline-content p {
        margin: 0;
        font-size: 14px;
    }
</style>
@endsection