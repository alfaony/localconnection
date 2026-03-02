@extends('adminlte::page')

@section('title', 'Edit Monthly Report')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit Monthly Report</h1>
            <p class="text-muted">{{ $partner->name }} - {{ $months[$month] }} {{ $target->year }}</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Partners</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.show', $partner) }}">{{ $partner->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.dashboard', ['partner' => $partner, 'year' => $target->year]) }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Edit Report</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
<form action="{{ route('partner.reports.update', ['partner' => $partner, 'target' => $target, 'month' => $month]) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar"></i> 
                        Editing Report for <strong>{{ $months[$month] }} {{ $target->year }}</strong>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        You are editing the report for <strong>{{ $months[$month] }}</strong>. 
                        Update the achievement values below.
                    </div>
                </div>
            </div>

            @foreach($target->targetValues as $index => $targetValue)
                @php
                    $report = $targetValue->getMonthlyReport($month, $target->year);
                    $previousValue = $report ? $report->achievement_value : 0;
                    $previousPercentage = $report ? $report->achievement_percentage : 0;
                @endphp
                
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar"></i> {{ $targetValue->parameterType->name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Target & Current Progress Info -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-bullseye"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Target</span>
                                        <span class="info-box-number">
                                            {{ number_format($targetValue->target_value, 0, ',', '.') }} {{ $targetValue->parameterType->unit }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">YTD Achievement</span>
                                        <span class="info-box-number">
                                            {{ number_format($targetValue->getTotalAchievement(), 0,',','.') }} 
                                            <small>({{ number_format($targetValue->getAchievementPercentage(), 0,',','.') }}%)</small>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($report)
                            <div class="alert alert-secondary">
                                <i class="fas fa-history"></i> 
                                <strong>Previous Value:</strong> {{ number_format($previousValue, 0,',','.') }} {{ $targetValue->parameterType->unit }}
                                ({{ number_format($previousPercentage, 0,',','.') }}% of monthly target)
                            </div>
                        @endif
                        
                        <input type="hidden" name="reports[{{ $index }}][target_value_id]" value="{{ $targetValue->id }}">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Achievement Value <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-md">
                                        <input type="number" 
                                               step="0.01" 
                                               class="form-control @error('reports.'.$index.'.achievement_value') is-invalid @enderror" 
                                               name="reports[{{ $index }}][achievement_value]"
                                               value="{{ old('reports.'.$index.'.achievement_value', $report ? $report->achievement_value : 0) }}"
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
                                              placeholder="Optional notes...">{{ old('reports.'.$index.'.notes', $report ? $report->notes : '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        @if($report)
                            <div class="row">
                                <div class="col-md-12">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> Last updated: {{ $report->updated_at->format('d M Y H:i') }}
                                        @if($report->reported_by)
                                            | Reported by: User #{{ substr($report->reported_by, 0, 8) }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-md">
                        <i class="fas fa-save"></i> Update Report
                    </button>
                    <a href="{{ route('partner.reports.manage', ['partner' => $partner, 'target' => $target]) }}" class="btn btn-default btn-md">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <a href="{{ route('partner.dashboard', ['partner' => $partner, 'year' => $target->year]) }}" class="btn btn-info btn-md float-right">
                        <i class="fas fa-chart-bar"></i> View Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Report Information</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">
                        <strong>Partner:</strong><br>
                        {{ $partner->name }}
                    </p>
                    <p class="text-muted mb-2">
                        <strong>Period:</strong><br>
                        {{ $months[$month] }} {{ $target->year }}
                    </p>
                    <p class="text-muted mb-2">
                        <strong>Parameters:</strong><br>
                        {{ $target->targetValues->count() }} parameter(s)
                    </p>
                </div>
            </div>

            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Year Progress</h3>
                </div>
                <div class="card-body">
                    @foreach($target->targetValues as $tv)
                        <div class="mb-3">
                            <strong>{{ $tv->parameterType->name }}</strong><br>
                            <small class="text-muted">
                                Target: {{ number_format($tv->target_value, 0,',','.') }} {{ $tv->parameterType->unit }}<br>
                                YTD: {{ number_format($tv->getTotalAchievement(), 0,',','.') }} {{ $tv->parameterType->unit }}<br>
                            </small>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $tv->getAchievementPercentage() >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                     style="width: {{ min($tv->getAchievementPercentage(), 100) }}%">
                                    {{ number_format($tv->getAchievementPercentage(), 0,',','.') }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Quick Navigation</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('partner.reports.manage', ['partner' => $partner, 'target' => $target]) }}" class="btn btn-block btn-default">
                        <i class="fas fa-calendar-alt"></i> All Months
                    </a>
                    @if($month > 1)
                        <a href="{{ route('partner.reports.edit', ['partner' => $partner, 'target' => $target, 'month' => $month - 1]) }}" 
                           class="btn btn-block btn-secondary">
                            <i class="fas fa-arrow-left"></i> Previous Month
                        </a>
                    @endif
                    @if($month < 12)
                        <a href="{{ route('partner.reports.edit', ['partner' => $partner, 'target' => $target, 'month' => $month + 1]) }}" 
                           class="btn btn-block btn-secondary">
                            <i class="fas fa-arrow-right"></i> Next Month
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
<style>
    .info-box {
        min-height: 80px;
    }
</style>
@stop