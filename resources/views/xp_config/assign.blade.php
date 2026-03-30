@extends('adminlte::page')

@section('title', 'Assign XP Config ke Company')

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route('xp-config.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="m-0 text-dark">🔗 Assign XP Config ke Company</h1>
            <small class="text-muted">Hubungkan konfigurasi XP ke company group Anda</small>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')

@if (session('update'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-check-circle mr-2"></i> Assign berhasil diperbarui!
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center">
            <i class="fas fa-building text-primary mr-2"></i>
            <h5 class="mb-0 font-weight-bold">Daftar Company</h5>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-4" style="width: 40%">Company</th>
                        <th style="width: 30%">XP Config Aktif</th>
                        <th style="width: 20%">Status XP</th>
                        <th class="text-right pr-4" style="width: 10%">Ubah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $company)
                    <tr>
                        <td class="pl-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 text-white font-weight-bold"
                                     style="width:36px;height:36px;background:linear-gradient(135deg,#667eea,#764ba2);flex-shrink:0;font-size:.85rem;">
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-weight-semibold text-dark">{{ $company->name }}</div>
                                    <small class="text-muted">{{ $company->slug ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($company->xpConfig)
                                <span class="badge badge-primary badge-pill px-3 py-1">
                                    ⚡ {{ $company->xpConfig->name }}
                                </span>
                            @else
                                <span class="badge badge-secondary badge-pill px-3 py-1">Belum diset</span>
                            @endif
                        </td>
                        <td>
                            @if($company->xpConfig && $company->xpConfig->is_enabled)
                                <span class="text-success"><i class="fas fa-check-circle mr-1"></i>Aktif</span>
                            @elseif($company->xpConfig)
                                <span class="text-warning"><i class="fas fa-pause-circle mr-1"></i>Nonaktif</span>
                            @else
                                <span class="text-muted"><i class="fas fa-minus-circle mr-1"></i>Tidak ada</span>
                            @endif
                        </td>
                        <td class="text-right pr-4">
                            <button class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                    data-target="#assignModal"
                                    onclick="openAssignModal('{{ $company->id }}', '{{ addslashes($company->name) }}', '{{ $company->xp_config_id ?? '' }}')">
                                <i class="fas fa-exchange-alt mr-1"></i> Assign
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignModalLabel">
                    <i class="fas fa-link mr-2"></i>Assign XP Config
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('xp-config.assign.update') }}" method="POST">
                @csrf
                <input type="hidden" name="company_id" id="modalCompanyId">
                <div class="modal-body">
                    <p class="text-muted mb-3">Company: <strong id="modalCompanyName"></strong></p>
                    <div class="form-group mb-0">
                        <label class="font-weight-semibold">Pilih XP Config</label>
                        <select name="xp_config_id" id="modalConfigSelect" class="form-control">
                            <option value="">-- Tidak Ada (Nonaktifkan XP) --</option>
                            @foreach($configs as $config)
                            <option value="{{ $config->id }}">{{ $config->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-1 d-block">Memilih kosong akan menonaktifkan XP untuk company ini.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    function openAssignModal(companyId, companyName, configId) {
        document.getElementById('modalCompanyId').value = companyId;
        document.getElementById('modalCompanyName').textContent = companyName;
        document.getElementById('modalConfigSelect').value = configId;
    }
</script>
@stop
