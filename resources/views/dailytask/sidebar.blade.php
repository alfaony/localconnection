<div class="row p-1">
    <div class="col-md-9">
        <strong>Improvement BOS > Development BAST</strong>
    </div>
    <div class="form-group row mb-0">
        <div class="col-md-5">
            <p class="form-control-plaintext">
                Ditugaskan
            </p> 
        </div>
        <div class="col-md-5">
            <p class="form-control-plaintext"><span class="badge badge badge-pill badge-info">{{ $dailytask->assign->name ?? '' }}</span></p>
        </div>
    </div>
    <div class="form-group row mb-0">
        <div class="col-md-5">
            <p class="form-control-plaintext">
                Tanggal
            </p> 
        </div>
        <div class="col-md-5">
            <p class="form-control-plaintext {{ $isOverdue ? 'text-danger' : '' }}">
                {{ $dailytask->dateShow }}
            </p> 
        </div>
    </div>
    @if($dailytask->status_submit)
    <div class="form-group row mb-0">
        <div class="col-md-5">
            <p class="form-control-plaintext">
            Status Submit
            </p> 
        </div>
        <div class="col-md-5">
            <p class="form-control-plaintext {{ $dailytask->status_submit == 'late' ? 'text-danger' : 'text-success' }}">
                {{ ucfirst($dailytask->status_submit) }}
            </p>
        </div>
    </div>
    @endif
    <div class="form-group row mb-0">
        <div class="col-md-5">
            <p class="form-control-plaintext">
                Status Tugas
            </p> 
        </div>
        <div class="col-md-5">
            <p class="form-control-plaintext">
                @switch($dailytask->taskStatus->name)
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
            </p>
        </div>
    </div>
    <div class="form-group row mb-0">
        <div class="col-md-5">
            <p class="form-control-plaintext">
                Tipe
            </p> 
        </div>
        <div class="col-md-5">
            <p class="form-control-plaintext">{{ $dailytask->type ? $dailytask->type->name : "" }}</p>
        </div>
    </div>
    @if(!empty($dailytask->recurring_days))
    <div class="form-group row mb-0">
        <div class="col-md-5">
            <p class="form-control-plaintext">
            Recurring Days
            </p> 
        </div>
        <div class="col-md-5">
            <div class="d-flex align-items-center mt-2">
                @php
                    $recurringDays = json_decode($dailytask->recurring_days, true);
                @endphp

                @foreach($recurringDays as $day)
                    <span class="badge badge-info mr-2 mb-2" style="font-size: 14px;">
                        {{ $daysMap[$day] ?? ucfirst($day) }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    <div class="form-group row mb-0">
        <div class="col-md-12">
            <p class="form-control-plaintext">
                Deskripsi :
            </p> 
        </div>
        <div class="col-md-12 card">
            <div class="card-body">
                    <div class="col-sm-8 ql-editor mt-2" style="max-height: 40vh; overflow-y: auto; white-space:unset; padding:0px 0px;">
                        {!! $dailytask->description !!}
                    </div>
            </div>
        </div>
    </div>
</div>