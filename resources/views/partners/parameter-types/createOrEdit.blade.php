@extends('adminlte::page')

@section('title', $isEdit ? 'Edit Tipe Parameter' : 'Tambah Tipe Parameter')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-{{ $isEdit ? 'edit' : 'plus-circle' }}"></i> 
                {{ $isEdit ? 'Edit' : 'Tambah' }} Tipe Parameter
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Master</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner-parameter-type.index') }}">Tipe Parameter</a></li>
                <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Tipe Parameter</h3>
                </div>
                <form action="{{ $isEdit ? route('partner-parameter-type.update', $parameterType) : route('partner-parameter-type.store') }}" 
                      method="POST">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">
                                        Nama <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $parameterType->name ?? '') }}" 
                                           placeholder="misal: Revenue, Deals, Sertifikasi"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Nama tampilan untuk parameter</small>
                                </div>
                            </div>

                            <!-- Code -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">
                                        Kode <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $parameterType->code ?? '') }}" 
                                           placeholder="misal: revenue, deals, certification"
                                           required
                                           {{ $isEdit ? 'readonly' : '' }}>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Identifikasi unik (huruf kecil, tanpa spasi)
                                        @if($isEdit)
                                            <br><strong class="text-warning">Kode tidak dapat diubah setelah dibuat</strong>
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <!-- Unit -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit">Satuan</label>
                                    <input type="text" 
                                           class="form-control @error('unit') is-invalid @enderror" 
                                           id="unit" 
                                           name="unit" 
                                           value="{{ old('unit', $parameterType->unit ?? '') }}" 
                                           placeholder="misal: IDR, count, person, %">
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Satuan pengukuran</small>
                                </div>
                            </div>

                            <!-- Sort Order -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Urutan</label>
                                    <input type="number" 
                                           class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" 
                                           name="sort_order" 
                                           value="{{ old('sort_order', $parameterType->sort_order ?? 0) }}" 
                                           min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Urutan tampilan (angka lebih kecil = prioritas lebih tinggi)</small>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Deskripsi</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3"
                                              placeholder="Deskripsi singkat mengenai parameter ini...">{{ old('description', $parameterType->description ?? '') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Is Active -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="icheck-primary">
                                        <input type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', $parameterType->is_active ?? true) ? 'checked' : '' }}>
                                        <label for="is_active">
                                            <strong>Aktif</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Hanya parameter aktif yang akan tersedia untuk dipilih pada target partner
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        @canAccess('update','partner_parameter_types')
                        @canAccess('store','partner_parameter_types')
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ $isEdit ? 'Perbarui' : 'Simpan' }} Tipe Parameter
                        </button>
                        @endcanAccess
                        @endcanAccess
                        <a href="{{ route('partner-parameter-type.index') }}" class="btn btn-default">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Info Card -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Panduan</h3>
                </div>
                <div class="card-body">
                    <h6><strong>Nama</strong></h6>
                    <p class="small text-muted">
                        Nama tampilan yang akan dilihat pengguna. Gunakan nama yang jelas dan deskriptif.
                        <br>Contoh: "Revenue", "Jumlah Deals"
                    </p>

                    <h6><strong>Kode</strong></h6>
                    <p class="small text-muted">
                        Identifikasi unik yang digunakan dalam sistem. Gunakan huruf kecil dengan garis bawah.
                        <br>Contoh: "revenue", "jumlah_deals"
                        @if($isEdit)
                            <br><span class="text-warning"><i class="fas fa-lock"></i> Terkunci setelah dibuat</span>
                        @endif
                    </p>

                    <h6><strong>Satuan</strong></h6>
                    <p class="small text-muted">
                        Satuan pengukuran untuk parameter ini.
                        <br>Contoh:
                        <br>• IDR (untuk mata uang)
                        <br>• count (untuk jumlah)
                        <br>• person (untuk jumlah orang)
                        <br>• % (untuk persentase)
                    </p>

                    <h6><strong>Urutan</strong></h6>
                    <p class="small text-muted">
                        Mengontrol urutan tampilan. Angka yang lebih kecil muncul lebih dulu.
                        <br>Contoh: 1, 2, 3, 4, 5
                    </p>
                </div>
            </div>

            @if($isEdit)
                <!-- Usage Info Card -->
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Statistik Penggunaan</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $usageCount = $parameterType->targetValues()->count();
                            $partnerCount = $parameterType->targetValues()
                                ->with('partnerTarget.partner')
                                ->get()
                                ->pluck('partnerTarget.partner.name')
                                ->unique()
                                ->count();
                        @endphp
                        
                        <p class="mb-2">
                            <strong>Digunakan pada:</strong>
                        </p>
                        <ul class="mb-0">
                            <li>{{ $usageCount }} nilai target</li>
                            <li>{{ $partnerCount }} partner</li>
                        </ul>

                        @if($usageCount > 0)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                <small>
                                    Parameter ini sedang digunakan dan tidak dapat dihapus.
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Examples Card -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb"></i> Contoh</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kode</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Revenue</td>
                                <td><code>revenue</code></td>
                                <td>IDR</td>
                            </tr>
                            <tr>
                                <td>Deals</td>
                                <td><code>deals</code></td>
                                <td>count</td>
                            </tr>
                            <tr>
                                <td>Sertifikasi</td>
                                <td><code>certification</code></td>
                                <td>count</td>
                            </tr>
                            <tr>
                                <td>Training Headcount</td>
                                <td><code>training_headcount</code></td>
                                <td>person</td>
                            </tr>
                            <tr>
                                <td>Pipeline Value</td>
                                <td><code>pipeline_value</code></td>
                                <td>IDR</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop