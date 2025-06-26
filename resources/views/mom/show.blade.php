@extends('adminlte::page')

@section('title', 'Detail MoM')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Minutes of Meeting (MoM)</h1>
        <div>
            <a href="{{ route('mom.edit', $mom->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit MoM
            </a>
            <a href="{{ route('mom.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- General Information Card -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle mr-2"></i>Meeting Information
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-info"><i class="far fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Meeting Date</span>
                            <span class="info-box-number">{{ $mom->mom_date }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-success"><i class="fas fa-project-diagram"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Project</span>
                            <span class="info-box-number">{{ $mom->project?->title ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-sticky-note mr-2"></i>Meeting Notes</h5>
                        <div class="p-3 border rounded bg-light">{!! $mom->notes !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agenda & Tasks Section -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks mr-2"></i>Meeting Agenda & Action Items
            </h3>
            <div class="card-tools">
                <span class="badge bg-primary">{{ $mom->agendas->count() }} Agendas</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="accordion" id="agendaAccordion">
                @foreach ($mom->agendas as $index => $agenda)
                <div class="card mb-2">
                    <div class="card-header bg-light" id="heading{{ $index }}">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-dark font-weight-bold d-flex justify-content-between w-100" 
                                    type="button" 
                                    data-toggle="collapse" 
                                    data-target="#collapse{{ $index }}" 
                                    aria-expanded="true" 
                                    aria-controls="collapse{{ $index }}">
                                <span>
                                    <i class="fas fa-clipboard-list mr-2"></i>
                                    {{ $agenda->title }}
                                </span>
                                <span>
                                    <span class="badge bg-info">{{ $agenda->tasks->count() }} Tasks</span>
                                    <i class="fas fa-chevron-down ml-2"></i>
                                </span>
                            </button>
                        </h5>
                    </div>

                    <div id="collapse{{ $index }}" 
                         class="collapse {{ $index === 0 ? 'show' : '' }}" 
                         aria-labelledby="heading{{ $index }}" 
                         data-parent="#agendaAccordion">
                        <div class="card-body">
                            <div class="callout callout-info mb-4">
                                <h5><i class="fas fa-comments mr-2"></i>Discussion Notes</h5>
                                <div class="p-3 border rounded bg-light">{!! nl2br(e($agenda->discussion_notes)) !!}</div>
                            </div>

                            @if ($agenda->tasks->count())
                            <div class="mt-4">
                                <h5><i class="fas fa-list-check mr-2"></i>Action Items</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Task</th>
                                                <th>Assigned To</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($agenda->tasks as $task)
                                            <tr>
                                                <td>{{ $task->title }}</td>
                                                <td>
                                                    @if ($task->user)
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-user mr-1"></i> {{ $task->user->name }}
                                                    </span>
                                                    @elseif ($task->external_email)
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-envelope mr-1"></i> {{ $task->external_email }}
                                                    </span>
                                                    @else
                                                    <span class="text-muted">Not assigned</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($task->end_date)
                                                    <span class="{{ \Carbon\Carbon::parse($task->end_date)->isPast() ? 'text-danger' : 'text-success' }}">
                                                        <i class="far fa-calendar mr-1"></i> {{ $task->end_date }}
                                                    </span>
                                                    @else
                                                    <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task->status)
                                                    <span class="badge 
                                                        @if($task->status->name === 'Completed') bg-success
                                                        @elseif($task->status->name === 'In Progress') bg-info
                                                        @elseif($task->status->name === 'Pending') bg-warning
                                                        @else bg-secondary @endif">
                                                        {{ $task->status->name ?? '-' }}
                                                    </span>
                                                    @else
                                                    <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>No action items for this agenda.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Meeting Participants -->
    @if (isset($mom->meeting->combined_participants) && count($mom->meeting->combined_participants) > 0)
    <div class="card card-info card-outline mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="fas fa-users mr-2"></i>Meeting Participants
                <span class="badge bg-primary ml-2">{{ count($mom->meeting->combined_participants) }} Participants</span>
            </h3>
            <div class="btn-group">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($mom->meeting->combined_participants as $participant)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card participant-card shadow-sm h-100 border-top-3 
                        {{ $participant['status'] === 'internal' ? 'border-primary' : 'border-warning' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="avatar-circle mr-3 bg-{{ $participant['status'] === 'internal' ? 'primary' : 'warning' }}">
                                    {{ strtoupper(substr($participant['name'], 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $participant['name'] }}</h5>
                                    <p class="text-muted mb-2 small">
                                        <i class="fas fa-envelope mr-1"></i> {{ $participant['email'] }}
                                    </p>
                                    
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-{{ $participant['status'] === 'internal' ? 'primary' : 'warning' }}">
                                            <i class="fas fa-{{ $participant['status'] === 'internal' ? 'user-tie' : 'globe' }} mr-1"></i>
                                            {{ $participant['status'] }}
                                        </span>
                                        
                                        @if(isset($participant['is_attended']))
                                        <span class="badge bg-{{ $participant['is_attended'] ? 'success' : 'danger' }}">
                                            <i class="fas fa-{{ $participant['is_attended'] ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                            {{ $participant['is_attended'] ? 'Hadir' : 'Tidak Hadir' }}
                                        </span>
                                        @endif
                                    </div>
                                    
                                    @if(isset($participant['join_time']) && $participant['join_time'])
                                    <div class="mt-2 text-sm text-muted">
                                        <i class="fas fa-clock mr-1"></i> Joined at {{ $participant['join_time'] }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@stop

@section('css')
<style>
    .info-box {
        border-radius: .25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125);
    }
    .card-outline {
        border-top: 3px solid #007bff;
    }
    .card-primary.card-outline {
        border-top-color: #007bff;
    }
    .card-success.card-outline {
        border-top-color: #28a745;
    }
    .card-info.card-outline {
        border-top-color: #17a2b8;
    }
    .accordion .card-header {
        padding: 0;
    }
    .accordion .btn-link {
        text-decoration: none;
        padding: 15px 20px;
    }
    .widget-user .widget-user-header {
        height: 120px;
        padding: 1rem;
        border-top-left-radius: .25rem;
        border-top-right-radius: .25rem;
    }
    .widget-user .widget-user-image {
        position: absolute;
        top: 85px;
        left: 50%;
        margin-left: -45px;
    }
    .widget-user img {
        width: 90px;
        height: 90px;
        border: 3px solid #fff;
    }
</style>
<style>
.participant-card {
    border-radius: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.participant-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
}

.border-top-3 {
    border-top-width: 3px !important;
}
</style>

@stop

@section('js')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Accordion icon rotation
        $('.accordion .btn-link').click(function() {
            $(this).find('.fa-chevron-down').toggleClass('rotate');
        });
    });
</script>
@stop