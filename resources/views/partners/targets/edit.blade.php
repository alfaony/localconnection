@extends('adminlte::page')

@section('title', 'Edit Target')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit Target {{ $target->year }} for {{ $partner->name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Partners</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.show', $partner) }}">{{ $partner->name }}</a></li>
                <li class="breadcrumb-item active">Edit Target</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<form action="{{ route('partner.targets.update', ['partner' => $partner, 'target' => $target]) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <!-- Basic Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Target Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="year">Year <span class="text-danger">*</span></label>
                                <select class="form-control @error('year') is-invalid @enderror" id="year" name="year_display" disabled>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ old('year', $target->year) == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <!-- Hidden input to preserve year value since disabled fields are not submitted -->
                                <input type="hidden" name="year" value="{{ $target->year }}">
                                <small class="text-muted">Year cannot be changed after target creation</small>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    @foreach($targetStatuses as $key => $value)
                                        <option value="{{ $key }}" {{ old('status', $target->status) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes', $target->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Target Parameters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Target Parameters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="addParameter()">
                            <i class="fas fa-plus"></i> Add Parameter
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="parametersContainer">
                        @php
                            $oldTargets = old('targets', $target->targetValues->map(function($tv) {
                                return [
                                    'parameter_type_id' => $tv->parameter_type_id,
                                    'target_value' => $tv->target_value,
                                    'description' => $tv->description,
                                ];
                            })->toArray());
                        @endphp

                        @foreach($oldTargets as $index => $targetValue)
                            <div class="parameter-row mb-3 p-3 border rounded" data-index="{{ $index }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Parameter <span class="text-danger">*</span></label>
                                            <select class="form-control parameter-select" name="targets[{{ $index }}][parameter_type_id]" required onchange="updateParameterOptions()">
                                                <option value="">-- Select --</option>
                                                @foreach($parameters as $param)
                                                    <option value="{{ $param->id }}" 
                                                            data-unit="{{ $param->unit }}"
                                                            {{ $targetValue['parameter_type_id'] == $param->id ? 'selected' : '' }}>
                                                        {{ $param->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Target Value <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" 
                                                   name="targets[{{ $index }}][target_value]" 
                                                   value="{{ $targetValue['target_value'] }}"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <input type="text" class="form-control" 
                                                   name="targets[{{ $index }}][description]"
                                                   value="{{ $targetValue['description'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-block" onclick="removeParameter(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Target
                    </button>
                    <a href="{{ route('partner.show', $partner) }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Warning</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-0">
                        Changing target parameters will reset all existing monthly reports for this target. 
                        Make sure to backup your data if needed.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('js')
<script>
let parameterIndex = {{ count($oldTargets) }};


// Update parameter options to disable already selected ones
function updateParameterOptions() {
    const allSelects = document.querySelectorAll('select[name*="parameter_type_id"]');
    const selectedValues = Array.from(allSelects)
        .map(select => select.value)
        .filter(value => value !== '');
    
    allSelects.forEach(select => {
        const currentValue = select.value;
        const options = select.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') return; // Skip empty option
            
            // Disable if selected elsewhere, but not if it's the current select's value
            if (selectedValues.includes(option.value) && option.value !== currentValue) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });
    });
}

function addParameter() {
    const container = document.getElementById('parametersContainer');
    const html = `
        <div class="parameter-row mb-3 p-3 border rounded" data-index="${parameterIndex}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Parameter <span class="text-danger">*</span></label>
                        <select class="form-control parameter-select" name="targets[${parameterIndex}][parameter_type_id]" required onchange="updateParameterOptions()">
                            <option value="">-- Select --</option>
                            @foreach($parameters as $param)
                                <option value="{{ $param->id }}" data-unit="{{ $param->unit }}">{{ $param->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Target Value <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="targets[${parameterIndex}][target_value]" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" class="form-control" name="targets[${parameterIndex}][description]">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-block" onclick="removeParameter(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    parameterIndex++;
    updateParameterOptions();
}

function removeParameter(button) {
    const rows = document.querySelectorAll('.parameter-row');
    if (rows.length > 1) {
        button.closest('.parameter-row').remove();
        updateParameterOptions();
    } else {
        alert('At least one parameter is required!');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateParameterOptions();
});
</script>
@stop