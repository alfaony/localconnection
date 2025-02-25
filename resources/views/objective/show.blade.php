@extends('adminlte::page')

@section('title', 'Objective Details')

@section('content_header')
    <h1>Objective Details - {{ $objective->name }}</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('objective.index') }}">Objective</a></li>
            <li class="breadcrumb-item active" aria-current="page"> {{ $objective->name ?? '' }}</li>
        </ol>
    </nav>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Key Results for {{ $objective->name ?? '' }}</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Key Result</th>
                            <th>Tanggal</th>
                            <th>Jumlah Tugas</th>
                            @canAccess('show','objectives')
                            <th>Actions</th>
                            @endcanAccess
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($objective->keyResults as $keyResult)
                            <tr>
                                <td>{{ $keyResult->result ?? ""}}</td>
                                <td>{{ $keyResult->dateShow ?? ""}}</td>
                                <td>{{ $keyResult->dailyTasks->count() ?? ""}}</td>
                                @canAccess('show','objectives')
                                <td>
                                    <a href="{{ route('objective.showtask', $keyResult->slug) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> Tugas</a>
                                </td>
                                @endcanAccess
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for each Key Result -->
    @foreach($objective->keyResults as $keyResult)
        <div class="modal fade" id="tasksModal{{ $keyResult->id }}" tabindex="-1" role="dialog" aria-labelledby="tasksModalLabel{{ $keyResult->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tasksModalLabel{{ $keyResult->id }}">Tasks for {{ $keyResult->result }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group">
                            @foreach($keyResult->dailyTasks as $task)
                                <li class="list-group-item"> <a href="{{ route('dailytask.show',$task->slug) }}" class="btn btn-info">{{ $task->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
