@extends('adminlte::page')

@section('title', 'Add Monthly Report')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-alt"></i> Add Monthly Report</h1>
            <p class="text-muted">{{ $partner->name }} - Target Year {{ $target->year }}</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Partners</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.show', $partner) }}">{{ $partner->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.dashboard', ['partner' => $partner, 'year' => $target->year]) }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Add Report</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
<form action="{{ route('partner.reports.store', ['partner' => $partner, 'target' => $target]) }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Select Month</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="month">Month <span class="text-danger">*</span></label>
                                <select class="form-control @error('month') is-invalid @enderror" id="month" name="month" required>
                                    <option value="">-- Select Month --</option>
                                    @foreach($months as $num => $name)
                                        <option value="{{ $num }}" 
                                            {{ old('month', request('month')) == $num ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Achievement Values</h3>
                </div>
                <div class="card-body">
                    @foreach($target->targetValues as $index => $targetValue)
                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar"></i> {{ $targetValue->parameterType->name }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-bullseye"></i> 
                                            Target: <strong>{{ number_format($targetValue->target_value, 0) }} {{ $targetValue->parameterType->unit }}</strong>
                                            <br>
                                            <small>Current Achievement: {{ number_format($targetValue->getTotalAchievement(), 0) }} {{ $targetValue->parameterType->unit }} ({{ number_format($targetValue->getAchievementPercentage(), 1) }}%)</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="reports[{{ $index }}][target_value_id]" value="{{ $targetValue->id }}">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Achievement Value <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control form-control-md @error('reports.'.$index.'.achievement_value') is-invalid @enderror" 
                                                       name="reports[{{ $index }}][achievement_value]"
                                                       value="{{ old('reports.'.$index.'.achievement_value', 0) }}"
                                                       placeholder="0"
                                                       required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">{{ $targetValue->parameterType->unit }}</span>
                                                </div>
                                            </div>
                                            @error('reports.'.$index.'.achievement_value')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea class="form-control" 
                                                      name="reports[{{ $index }}][notes]"
                                                      rows="3"
                                                      placeholder="Optional notes...">{{ old('reports.'.$index.'.notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-md">
                        <i class="fas fa-save"></i> Save Report
                    </button>
                    <a href="{{ route('partner.reports.manage', ['partner' => $partner, 'target' => $target]) }}" class="btn btn-default btn-md">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Information</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">
                        <strong>Partner:</strong><br>
                        {{ $partner->name }}
                    </p>
                    <p class="text-muted mb-2">
                        <strong>Target Year:</strong><br>
                        {{ $target->year }}
                    </p>
                    <p class="text-muted mb-2">
                        <strong>Parameters:</strong><br>
                        {{ $target->targetValues->count() }} parameter(s) to report
                    </p>
                    <hr>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-lightbulb"></i>
                        <small>
                            If a report for this month already exists, it will be updated with the new values.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Target Summary</h3>
                </div>
                <div class="card-body">
                    @foreach($target->targetValues as $tv)
                        <div class="mb-3">
                            <strong>{{ $tv->parameterType->name }}</strong><br>
                            <small class="text-muted">
                                Target: {{ number_format($tv->target_value, 0) }} {{ $tv->parameterType->unit }}<br>
                                Current: {{ number_format($tv->getTotalAchievement(), 0) }} {{ $tv->parameterType->unit }}<br>
                            </small>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $tv->getAchievementPercentage() >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                     style="width: {{ min($tv->getAchievementPercentage(), 100) }}%">
                                    {{ number_format($tv->getAchievementPercentage(), 1) }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>
@stop