@extends('adminlte::page')

@section('title', $partner->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-building"></i> {{ $partner->name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Partners</a></li>
                <li class="breadcrumb-item active">{{ $partner->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="row mb-3">
        <div class="col-12">
            @canAccess('dashboard','partner_dashboards')
            <a href="{{ route('partner.dashboard', $partner) }}" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            @endcanAccess
            @canAccess('edit','partners')
            <a href="{{ route('partner.edit', $partner) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcanAccess
        </div>
    </div>

    <!-- Partner Details -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Partner Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Type:</th>
                            <td><span class="badge badge-info">{{ $partner->partner_type_name }}</span></td>
                        </tr>
                        <tr>
                            <th>Industry:</th>
                            <td>{{ $partner->industry ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Website:</th>
                            <td>
                                @if($partner->website)
                                    <a href="{{ $partner->website }}" target="_blank">
                                        {{ $partner->website }} <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($partner->status == 'active')
                                    <span class="badge badge-success">{{ ucfirst($partner->status) }}</span>
                                @elseif($partner->status == 'inactive')
                                    <span class="badge badge-secondary">{{ ucfirst($partner->status) }}</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($partner->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Partnership Started:</th>
                            <td>{{ $partner->partnership_started_at ? $partner->partnership_started_at->format('d M Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-certificate"></i> Certification</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Certified:</th>
                            <td>
                                @if($partner->is_certified)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        @if($partner->is_certified)
                            <tr>
                                <th>Level:</th>
                                <td>{{ ucfirst($partner->certification_level) ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Certified Date:</th>
                                <td>{{ $partner->certified_at ? $partner->certified_at->format('d M Y') : '-' }}</td>
                            </tr>
                            @if($partner->certification_file)
                                <tr>
                                    <th>Certificate File:</th>
                                    <td>
                                        <a href="{{ s3_asset(10,true,$partner->certification_file) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> Download Certificate
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Targets -->
    <div class="card">
        @canAccess('create','partner_targets')
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullseye"></i> Targets</h3>
            <div class="card-tools">
                <a href="{{ route('partner.targets.create', $partner) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add Target
                </a>
            </div>
        </div>
        @endcanAccess
        <div class="card-body">
            @if($partner->targets->count() > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Parameters</th>
                            <th>Progress</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partner->targets->sortByDesc('year') as $target)
                            <tr>
                                <td><strong>{{ $target->year }}</strong></td>
                                <td>
                                    @if($target->status == 'active')
                                        <span class="badge badge-success">{{ ucfirst($target->status) }}</span>
                                    @elseif($target->status == 'completed')
                                        <span class="badge badge-info">{{ ucfirst($target->status) }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($target->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    
                                        @foreach($target->targetValues as $value)
                                            <span class="badge badge-light font-weight-bold mr-1 mb-1">
                                                {{ $value->parameterType->name }}: {{ number_format($value->target_value) }}
                                            </span>
                                        @endforeach
                                    
                                </td>
                                <td>
                                    @php
                                        $totalAchievement = 0;
                                        $totalTarget = 0;
                                        foreach($target->targetValues as $value) {
                                            $totalTarget += $value->target_value;
                                            $totalAchievement += $value->getTotalAchievement();
                                        }
                                        $percentage = $totalTarget > 0 ? ($totalAchievement / $totalTarget) * 100 : 0;
                                    @endphp
                                    <div class="progress">
                                        <div class="progress-bar {{ $percentage >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                             role="progressbar" 
                                             style="width: {{ min($percentage, 100) }}%">
                                            {{ number_format($percentage, 1) }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        @canAccess('dashboard','partner_dashboards')
                                        <a href="{{ route('partner.dashboard', ['partner' => $partner, 'year' => $target->year]) }}" 
                                           class="btn btn-sm btn-info mb-1 mr-1" title="Dashboard">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                        @endcanAccess

                                        @canAccess('manage','partner_monthly_reports')
                                        <a href="{{ route('partner.reports.manage', ['partner' => $partner, 'target' => $target]) }}" 
                                           class="btn btn-sm btn-success mb-1 mr-1" title="Manage Reports">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        @endcanAccess

                                        @canAccess('edit','partner_targets')
                                        <a href="{{ route('partner.targets.edit', ['partner' => $partner, 'target' => $target]) }}" 
                                           class="btn btn-sm btn-warning mb-1 mr-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcanAccess
                                        @canAccess('destroy','partner_targets')
                                        <form action="{{ route('partner.targets.destroy', ['partner' => $partner, 'target' => $target]) }}" 
                                              method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1 mr-1" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcanAccess
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No targets set yet.</p>
                    <a href="{{ route('partner.targets.create', $partner) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Target
                    </a>
                </div>
            @endif
        </div>
    </div>
@stop