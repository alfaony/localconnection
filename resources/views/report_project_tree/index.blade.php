@extends('adminlte::page')

@section('title', 'Task Tracking')

@section('content')
<div id="accordion">
@canAccess('fetchusertask', 'project_dashboards')
  <div class="card p-3 mt-3">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Pantauan Tugas
        </button>
      </h5>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
        <div class="card-body">
            <div class="accordion" id="taskAccordion">
                <form method="GET" action="{{ route('projectdashboard.index') }}" class="mb-3">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control select2" name="division_id">
                                <option value="" selected>-- Divisi --</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
                <div class="card">
                    <div class="card-header" id="headingOverdue">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOverdue" aria-expanded="true" aria-controls="collapseOverdue">
                                Overdue Tasks
                            </button>
                        </h5>
                    </div>
                    <div id="collapseOverdue" class="collapse show" aria-labelledby="headingOverdue" data-parent="#taskAccordion">
                        <div class="card-body">
                            <canvas id="overdueTasksChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingUpcoming">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseUpcoming" aria-expanded="false" aria-controls="collapseUpcoming">
                                Upcoming Tasks
                            </button>
                        </h5>
                    </div>
                    <div id="collapseUpcoming" class="collapse" aria-labelledby="headingUpcoming" data-parent="#taskAccordion">
                        <div class="card-body">
                            <canvas id="upcomingTasksChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <h3>Tasks : <span id="userName"></span></h3>
                <div id="userTasks"></div>
            </div>
        </div>
    </div>

  </div>
  @endcanAccess
  <div class="card mt-2">
    <div class="card-header" id="headingTwo">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            Pantauan Visi 
        </button>
      </h5>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
      <div class="card-body">
        <h2 class="mb-4">Task Tracking</h2>
            <div class="card-body accordion" id="visionAccordion">
                @foreach($visions as $vision)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" id="headingVision{{ $vision->id }}">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseVision{{ $vision->id }}" aria-expanded="true" aria-controls="collapseVision{{ $vision->id }}">
                                Visi: {{ $vision->vision }} 
                                ({{ $vision->missions->sum(function($mission) {
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
                                <h5 class="p-3">Misi</h5>
                                @foreach($vision->missions as $mission)
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center" id="headingMission{{ $mission->id }}">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseMission{{ $mission->id }}" aria-expanded="false" aria-controls="collapseMission{{ $mission->id }}">
                                                {{ $mission->mission }} ({{ $mission->objectives->sum(function($objective) {
                                                    return $objective->keyResults->sum(function($keyResult) {
                                                        return $keyResult->dailyTasks->count();
                                                    });
                                                }) }} Task)
                                            </button>
                                        </h5>
                        
                                    </div>
                                    <div id="collapseMission{{ $mission->id }}" class="collapse" aria-labelledby="headingMission{{ $mission->id }}" data-parent="#missionAccordion{{ $vision->id }}">
                                        <div class="card-body">
                                            <h5 class="p-3">Objective</h5>
                                            <div class="accordion" id="objectiveAccordion{{ $mission->id }}">
                                                @foreach($mission->objectives as $objective)
                                                <div class="card">
                                                    <div class="card-header d-flex justify-content-between align-items-center" id="headingObjective{{ $objective->id }}">
                                                        <h5 class="mb-0">
                                                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseObjective{{ $objective->id }}" aria-expanded="false" aria-controls="collapseObjective{{ $objective->id }}">
                                                                {{ $objective->name }} ({{ $objective->keyResults->sum(function($keyResult) {
                                                                    return $keyResult->dailyTasks->count();
                                                                }) }} Task)
                                                            </button>
                                                        </h5>
                                        
                                                    </div>
                                                    <div id="collapseObjective{{ $objective->id }}" class="collapse" aria-labelledby="headingObjective{{ $objective->id }}" data-parent="#objectiveAccordion{{ $mission->id }}">
                                                        <div class="card-body">
                                                            <h5 class="p-3" >Key Result</h5>
                                                            <div class="accordion" id="keyResultAccordion{{ $objective->id }}">
                                                                @foreach($objective->keyResults as $keyResult)
                                                                <div class="card">
                                                                    <div class="card-header d-flex justify-content-between align-items-center" id="headingKeyResult{{ $keyResult->id }}">
                                                                        <h5 class="mb-0">
                                                                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseKeyResult{{ $keyResult->id }}" aria-expanded="false" aria-controls="collapseKeyResult{{ $keyResult->id }}">
                                                                                {{ $keyResult->result }} ({{ $keyResult->dailyTasks->count() }} Task)
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
            </div>
        </div>
        </div>
@endsection
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data for Overdue Tasks
    var overdueLabels = @json($overdueTasks->pluck('name'));
    var overdueData = @json($overdueTasks->pluck('daily_task_assigns_count'));
    
    // Data for Upcoming Tasks
    var upcomingLabels = @json($upcomingTasks->pluck('name'));
    var upcomingData = @json($upcomingTasks->pluck('daily_task_assigns_count'));

    // Overdue Tasks Chart
    var ctxOverdue = document.getElementById('overdueTasksChart').getContext('2d');
    var overdueTasksChart = new Chart(ctxOverdue, {
        type: 'bar',
        data: {
            labels: overdueLabels,
            datasets: [{
                label: 'Overdue Tasks',
                data: overdueData,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            onClick: function(e, elements) {
                if (elements.length > 0) {
                    var index = elements[0].index;
                    var userName = overdueLabels[index];
                    var userId = @json($overdueTasks->pluck('id'))[index];
                    console.log('User ID:', userId, 'User Name:', userName);
                    fetchUserTasks(userId, userName, 'overdue');
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    // Upcoming Tasks Chart
    var ctxUpcoming = document.getElementById('upcomingTasksChart').getContext('2d');
    var upcomingTasksChart = new Chart(ctxUpcoming, {
        type: 'bar',
        data: {
            labels: upcomingLabels,
            datasets: [{
                label: 'Upcoming Tasks',
                data: upcomingData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            onClick: function(e, elements) {
                if (elements.length > 0) {
                    var index = elements[0].index;
                    var userName = upcomingLabels[index];
                    var userId = @json($upcomingTasks->pluck('id'))[index];
                    fetchUserTasks(userId, userName,'upcoming');
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    function fetchUserTasks(userId, userName, filter = 'overdue') 
    {
        document.getElementById('userName').innerText = userName;
        document.getElementById('userTasks').innerHTML = 'Loading...';
        
        let url = `{{ route('fetchusertask', [':userId', ':filter']) }}`;
        url = url.replace(':userId', userId);
        url = url.replace(':filter', filter);

        fetch(url.replace(':userId', userId))
            .then(response => response.json())
            .then(tasks => {
                let tasksHtml = '<table class="table table-bordered"><thead><tr><th>Tugas</th><th>Status</th><th>Tanggal</th><th>Dibuat</th><th>Action</th></tr></thead><tbody>';
                tasks.forEach(task => {
                    let statusIcon = '';
                    let url = '';
                    switch (task.task_status.name) {
                        case 'todo':
                            statusIcon = '<i class="fa fa-list-alt"></i>';
                            break;
                        case 'doing':
                            statusIcon = '<i class="fa fa-hourglass-start"></i>';
                            break;
                        case 'in review':
                            statusIcon = '<i class="fa fa-eye" style="color: green;"></i>';
                            break;
                        case 'not complete':
                            statusIcon = '<i class="fa fa-times-circle" style="color: red;"></i>';
                            break;
                        case 'complete':
                            statusIcon = '<i class="fa fa-check" style="color: green;"></i>';
                            break;
                        default:
                            statusIcon = task.task_status.name;
                    }

                    if(task)
                    {
                        url = `<a href="${task.url}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>`;
                    }else
                    {
                        url = '-';
                    }
                    
                    if(task.is_overdue)
                    {
                        date_show = `<span class="text-danger"> ${task.date_show }</span>`;
                    }else
                    {
                        date_show = `<span> ${task.date_show}</span>`;
                    }


                    tasksHtml += `<tr>
                                    <td>${task.name_show}</td>
                                    <td>${statusIcon} ${task.task_status.name}</td>
                                    <td>${date_show}</td>
                                    <td>${task.user_create}</td>
                                    <td>
                                        ${url}
                                    </td>
                                </tr>`;
                });
                tasksHtml += '</tbody></table>';
                document.getElementById('userTasks').innerHTML = tasksHtml;
            })
            .catch(error => {
                document.getElementById('userTasks').innerHTML = 'Error fetching tasks.';
                console.error('Error fetching tasks:', error);
        });
    }
});
</script>
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
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
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
        border: 2px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 2px solid #007bff !important;
    }
    #userTasks 
    {
        max-height: 500px;
        overflow-y: auto;
        margin-top: 20px;
    }

    .accordion .card-header {
        cursor: pointer;
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
