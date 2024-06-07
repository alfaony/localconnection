@extends('adminlte::page')

@section('title', 'Task Tracking')

@section('content')
<div class="card py-3 mt-3 ">
    <h2 class="card-body mb-4">Task Tracking</h2>
    <div class="accordion" id="visionAccordion">
        @foreach($visions as $vision)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" id="headingVision{{ $vision->id }}">
                <h5 class="mb-0">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseVision{{ $vision->id }}" aria-expanded="true" aria-controls="collapseVision{{ $vision->id }}">
                        Visi: {{ $vision->name }} ({{ $vision->missions->sum(function($mission) {
                            return $mission->objectives->sum(function($objective) {
                                return $objective->keyResults->sum(function($keyResult) {
                                    return $keyResult->dailyTasks->count();
                                });
                            });
                        }) }} Task)
                    </button>
                </h5>

            </div>

            <div id="collapseVision{{ $vision->id }}" class="collapse" aria-labelledby="headingVision{{ $vision->id }}" data-parent="#visionAccordion">
                <div class="card-body">
                    <div class="accordion" id="missionAccordion{{ $vision->id }}">
                        @foreach($vision->missions as $mission)
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center" id="headingMission{{ $mission->id }}">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseMission{{ $mission->id }}" aria-expanded="false" aria-controls="collapseMission{{ $mission->id }}">
                                        Misi: {{ $mission->name }} ({{ $mission->objectives->sum(function($objective) {
                                            return $objective->keyResults->sum(function($keyResult) {
                                                return $keyResult->dailyTasks->count();
                                            });
                                        }) }} Task)
                                    </button>
                                </h5>
                
                            </div>
                            <div id="collapseMission{{ $mission->id }}" class="collapse" aria-labelledby="headingMission{{ $mission->id }}" data-parent="#missionAccordion{{ $vision->id }}">
                                <div class="card-body">
                                    <div class="accordion" id="objectiveAccordion{{ $mission->id }}">
                                        @foreach($mission->objectives as $objective)
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center" id="headingObjective{{ $objective->id }}">
                                                <h5 class="mb-0">
                                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseObjective{{ $objective->id }}" aria-expanded="false" aria-controls="collapseObjective{{ $objective->id }}">
                                                        Objective: {{ $objective->name }} ({{ $objective->keyResults->sum(function($keyResult) {
                                                            return $keyResult->dailyTasks->count();
                                                        }) }} Task)
                                                    </button>
                                                </h5>
                                
                                            </div>
                                            <div id="collapseObjective{{ $objective->id }}" class="collapse" aria-labelledby="headingObjective{{ $objective->id }}" data-parent="#objectiveAccordion{{ $mission->id }}">
                                                <div class="card-body">
                                                    <div class="accordion" id="keyResultAccordion{{ $objective->id }}">
                                                        @foreach($objective->keyResults as $keyResult)
                                                        <div class="card">
                                                            <div class="card-header d-flex justify-content-between align-items-center" id="headingKeyResult{{ $keyResult->id }}">
                                                                <h5 class="mb-0">
                                                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseKeyResult{{ $keyResult->id }}" aria-expanded="false" aria-controls="collapseKeyResult{{ $keyResult->id }}">
                                                                        Key Result: {{ $keyResult->name }} ({{ $keyResult->dailyTasks->count() }} Task)
                                                                    </button>
                                                                </h5>
                                                
                                                            </div>
                                                            <div id="collapseKeyResult{{ $keyResult->id }}" class="collapse" aria-labelledby="headingKeyResult{{ $keyResult->id }}" data-parent="#keyResultAccordion{{ $objective->id }}">
                                                                <div class="card-body">
                                                                    <ul class="list-group">
                                                                            <div class="table-responsive">
                                                                                <table class="table table-striped table-bordered">
                                                                                    <thead class="thead-light">
                                                                                        <tr>
                                                                                            <th>Tugas</th>
                                                                                            <th>Tanggal</th>
                                                                                            <th>Status</th>
                                                                                            <th>Ditugaskan</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                    @foreach($keyResult->dailyTasks as $dailyTask)
                                                                            @php
                                                                                $isOverdueSub = $dailyTask->isOverdue();
                                                                            @endphp
                                                                                        <tr>
                                                                                            <td>
                                                                                                @canAccess('show', 'dailytasks')
                                                                                                <a href="{{ route('dailytask.show', $dailyTask->slug) }}">{{ Str::limit($dailyTask->name) }}</a>
                                                                                                @endcanAccess
                                                                                            </td>
                                                                                            <td>
                                                                                                <span class="{{ $isOverdueSub ? 'text-danger' : '' }}">
                                                                                                    {{ $dailyTask->dateShow }}
                                                                                                </span>
                                                                                            </td>
                                                                                            <td>
                                                                                                @switch($dailyTask->taskStatus->name)
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
                                                                                                        {{ $dailyTask->taskStatus->name }}
                                                                                                @endswitch
                                                                                            </td>
                                                                                            <td>
                                                                                                {{ $dailyTask->assign ? $dailyTask->assign->name : '' }}
                                                                                            </td>
                                                                                        </tr>
                                                                                        @endforeach
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@section('css')
<style>
.card-header .btn-link {
    color: #007bff;
    text-decoration: none;
}
.card-header .btn-link:hover {
    text-decoration: underline;
}
.card-header i {
    transition: transform 0.3s ease;
}
.card-header .collapsed .fa-chevron-right {
    transform: rotate(0);
}
.card-header[aria-expanded="true"] .fa-chevron-right {
    transform: rotate(90deg);
}
.list-group-item {
    border: none;
    padding-left: 2rem;
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
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('.collapse').on('show.bs.collapse', function () {
            $(this).parent().find('.rotate-icon').addClass('rotate');
        }).on('hide.bs.collapse', function () {
            $(this).parent().find('.rotate-icon').removeClass('rotate');
        });
    });
</script>
@endsection
