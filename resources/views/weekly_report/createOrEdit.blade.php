@extends('adminlte::page')

@section('title', $mode === 'edit' ? 'Edit Laporan Mingguan' : 'Buat Laporan Mingguan')

@section('content_header')
    <h1 class="m-0 text-dark">{{ $mode === 'edit' ? '✏️ Edit Laporan Mingguan' : '📝 Buat Laporan Mingguan' }}</h1>
@stop

@section('content')
@include('components.alert')
<form action="{{ $mode === 'edit' ? route('weekly-report.update', $report->id) : route('weekly-report.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="m-0">
                <i class="fas fa-file-alt mr-2"></i>Form Laporan Mingguan
            </h5>
        </div>
        
        <div class="card-body">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6 border-right">
                    <!-- Divisi -->
                    <div class="form-group">
                        <label for="division_id" class="font-weight-bold">
                            <i class="fas fa-building mr-2"></i>Divisi
                        </label>
                        <select name="division_id" id="division_id" class="form-control select2" required>
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($userDivisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id', $report->division_id ?? '') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Picker -->
                    <div class="form-group">
                        <label for="date" class="font-weight-bold">
                            <i class="fas fa-calendar-alt mr-2"></i>Tanggal Laporan
                        </label>
                        <input type="date"  class="form-control" 
                            value="{{ old('date', isset($report) ? $report->date->format('Y-m-d') : now()->format('Y-m-d')) }}" readonly>
                    </div>

                    <!-- Tahun & Minggu -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info p-2">
                                <small class="font-weight-bold">
                                    <i class="fas fa-calendar mr-2"></i>Tahun<br>
                                    <span class="h5">{{ now()->year }}</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning p-2">
                                <small class="font-weight-bold">
                                    <i class="fas fa-clock mr-2"></i>Minggu ke<br>
                                    <span id="week" class="h5">-</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <!-- Text Areas -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-tasks mr-2 text-success"></i>Aktivitas Kunci
                        </label>
                        <input class="thriveEditor form-control" id="description_key_activities" data-ids="key_activities" name="key_activities" placeholder="Masukkan aktivitas utama minggu ini..."/>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>Permasalahan
                        </label>
                        <input class="thriveEditor form-control" id="description_problems" data-ids="problems" name="problems" placeholder="Deskripsi tantangan/hambatan..."/>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-bullseye mr-2 text-primary"></i>Target
                        </label>
                        <input class="thriveEditor form-control" id="description_targets" data-ids="targets" name="targets" placeholder="Rencana target minggu depan..."/>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-file mr-2 text-secondary"></i>File Laporan
                        </label>
                        <input type="file" name="file" class="form-control" 
                            accept=".pdf" aria-describedby="file-name">
                    </div>
                    @if($mode === 'edit' && $report->file)
                        <div class="form-group">
                            <label>File Sebelumnya:</label><br>
                            <a href="{{ asset('storage/' . $report->file) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-file-pdf mr-1"></i>Lihat File
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="my-4">

            <!-- Metrics Section -->
            <div class="metrics-section">
                <h5 class="mb-3 font-weight-bold">
                    <i class="fas fa-chart-line mr-2 text-info"></i>Metrik Kinerja
                </h5>
                
                <div class="row">
                    @php
                        $metrics = [
                            'number_of_customers' => ['icon' => 'users', 'color' => 'success', 'label' => 'Pelanggan Aktif'],
                            'number_of_users' => ['icon' => 'user-friends', 'color' => 'info', 'label' => 'User Sistem'],
                            'number_of_products' => ['icon' => 'box-open', 'color' => 'warning', 'label' => 'Produk'],
                            'number_of_projects' => ['icon' => 'project-diagram', 'color' => 'primary', 'label' => 'Proyek'],
                            'number_of_homepasses' => ['icon' => 'home', 'color' => 'secondary', 'label' => 'Homepass'],
                            'number_of_leads' => ['icon' => 'chart-line', 'color' => 'danger', 'label' => 'Leads'],
                            'number_of_views' => ['icon' => 'eye', 'color' => 'dark', 'label' => 'Views'],
                            'number_of_profit' => ['icon' => 'dollar-sign', 'color' => 'success', 'label' => 'Profit'],
                        ];
                    @endphp

                    @foreach($metrics as $name => $data)
                        <div class="col-md-3 mb-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-{{ $data['color'] }} text-white">
                                        <i class="fas fa-{{ $data['icon'] }}"></i>
                                    </span>
                                </div>
                                <input type="number" name="{{ $name }}" id="{{ $name }}" 
                                    class="form-control" placeholder="{{ $data['label'] }}"
                                    value="{{ old($name, $report->$name ?? '') }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 text-right">
                <a href="{{ route('weekly-report.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <button type="submit" class="btn btn-{{ $mode === 'edit' ? 'warning' : 'primary' }}">
                    <i class="fas fa-{{ $mode === 'edit' ? 'save' : 'check' }} mr-2"></i>
                    {{ $mode === 'edit' ? 'Update' : 'Simpan' }}
                </button>
            </div>
        </div>
    </div>
</form>
@stop

@section('js')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    // Auto set year & week dari tanggal
    document.getElementById('date').addEventListener('change', function () {
        const selectedDate = new Date(this.value);
        const year = selectedDate.getFullYear();

        const janFirst = new Date(selectedDate.getFullYear(), 0, 1);
        const weekNumber = Math.ceil((((selectedDate - janFirst) / 86400000) + janFirst.getDay() + 1) / 7);

        document.getElementById('week').innerHTML = weekNumber;
    });

    document.getElementById('date').dispatchEvent(new Event('change'));
</script>
@stop

@section('css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        padding: 5px;
    }
    .metrics-section .input-group-text {
        min-width: 45px;
        justify-content: center;
    }
    textarea {
        resize: none;
        min-height: 100px;
    }
    .alert small {
        font-size: 0.85rem;
    }
</style>
@stop