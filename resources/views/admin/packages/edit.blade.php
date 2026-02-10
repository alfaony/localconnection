@extends('adminlte::page')

@section('title', 'Edit Package')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Edit Package: {{ $package->nama_paket }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('software.index') }}">Software</a></li>
                <li class="breadcrumb-item"><a href="{{ route('software.show', $software->id) }}">{{ $software->nama }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('software.packages.index', $software->id) }}">Packages</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Package Form</h3>
            <div class="card-tools">
                <a href="{{ route('software.packages.index', $software->id) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <form action="{{ route('software.packages.update', [$software->id, $package->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_paket">Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="nama_paket" 
                                   id="nama_paket" 
                                   class="form-control @error('nama_paket') is-invalid @enderror" 
                                   value="{{ old('nama_paket', $package->nama_paket) }}"
                                   placeholder="e.g., 1 Bulan, 3 Bulan, 6 Bulan">
                            @error('nama_paket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Name of the package (e.g., Monthly, Quarterly)</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="durasi_hari">Durasi (Hari) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="durasi_hari" 
                                   id="durasi_hari" 
                                   class="form-control @error('durasi_hari') is-invalid @enderror" 
                                   value="{{ old('durasi_hari', $package->durasi_hari) }}"
                                   min="1"
                                   placeholder="e.g., 30, 90, 180">
                            @error('durasi_hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Duration in days (Current: {{ $package->durasi_hari }} days ≈ {{ $package->duration_in_months }} months)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="harga">Harga (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="number" 
                                       name="harga" 
                                       id="harga" 
                                       class="form-control @error('harga') is-invalid @enderror" 
                                       value="{{ old('harga', $package->harga) }}"
                                       min="0"
                                       placeholder="50000">
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Current: {{ $package->formatted_price }}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" 
                                    id="status" 
                                    class="form-control @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $package->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $package->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Package availability status</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" 
                              id="description" 
                              class="form-control @error('description') is-invalid @enderror" 
                              rows="3"
                              placeholder="Optional description for this package">{{ old('description', $package->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Optional package description or benefits</small>
                </div>

                <!-- Quick Calculate Helper -->
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">💡 Quick Calculator</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>Months:</label>
                                    <input type="number" id="calc_months" class="form-control form-control-sm" value="1" min="1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>Days:</label>
                                    <input type="text" id="calc_days" class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>&nbsp;</label>
                                <button type="button" id="apply_duration" class="btn btn-sm btn-info btn-block">
                                    Apply to Duration Field
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Quick helper: 1 month ≈ 30 days</small>
                    </div>
                </div>

                <!-- Package Stats (if exists) -->
                @if($package->exists)
                    <div class="card bg-info">
                        <div class="card-body">
                            <h5 class="card-title">📊 Package Statistics</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Total Subscriptions:</strong>
                                    <p class="mb-0">{{ $package->subscriptions()->count() }}</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Active Subscriptions:</strong>
                                    <p class="mb-0">{{ $package->subscriptions()->where('status', 'active')->count() }}</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Revenue:</strong>
                                    <p class="mb-0">Rp {{ number_format($package->subscriptions()->count() * $package->harga, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-footer">
                @canAccess('update', 'software_packages')
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Package
                </button>
                @endcanAccess
                <a href="{{ route('software.packages.index', $software->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Duration calculator
    $('#calc_months').on('input', function() {
        const months = parseInt($(this).val()) || 0;
        const days = months * 30;
        $('#calc_days').val(days);
    }).trigger('input');

    $('#apply_duration').on('click', function() {
        const days = $('#calc_days').val();
        $('#durasi_hari').val(days);
    });

    // Format price input
    $('#harga').on('blur', function() {
        let value = $(this).val();
        if(value) {
            value = value.replace(/[^0-9]/g, '');
            $(this).val(value);
        }
    });
});
</script>
@stop
