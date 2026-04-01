@extends('adminlte::page')

@section('title', 'Kirim Badge')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">🎖️ Kirim Badge</h1>
        <small style="color:#a0a8d0;">Berikan penghargaan kepada karyawan</small>
    </div>
    <a href="{{ route('badge.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
        <i class="fas fa-arrow-left mr-1"></i> Master Badge
    </a>
</div>
@stop

@section('content')
@include('components.alert')

<div class="row g-4">

    {{-- FORM KIRIM --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-lg h-100" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:4px;background:linear-gradient(90deg,#667eea,#f093fb,#f5a623);"></div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4" style="color:#c8d0e0;"><i class="fas fa-paper-plane me-2" style="color:#f093fb;"></i> Pilih Badge & Penerima</h6>

                <form action="{{ route('badge.assign.store') }}" method="POST">
                    @csrf

                    {{-- Badge Picker --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Badge</label>
                        <div class="row g-2" id="badge-picker">
                            @foreach($badges as $b)
                            <div class="col-4">
                                <label class="badge-option w-100" style="cursor:pointer;">
                                    <input type="radio" name="badge_id" value="{{ $b->id }}" class="d-none badge-radio"
                                           {{ old('badge_id') == $b->id ? 'checked' : '' }}>
                                    <div class="badge-card text-center p-2 rounded-3 @error('badge_id') border-danger @enderror"
                                         style="background:rgba(255,255,255,.05);border:2px solid rgba(255,255,255,.08);transition:all .2s;">
                                        <div class="mb-1" style="height:44px;display:flex;align-items:center;justify-content:center;">
                                            @if($b->image)
                                            <img src="{{ s3_asset(true, null, $b->image) }}" style="height:36px;width:36px;object-fit:contain;" alt="{{ $b->name }}">
                                            @else
                                            <span style="font-size:1.6rem;">🏅</span>
                                            @endif
                                        </div>
                                        <div style="font-size:.65rem;color:#a0a8d0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $b->name }}</div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('badge_id')<small class="text-danger">{{ $message }}</small>@enderror
                        @if($badges->isEmpty())
                        <p style="color:#a0a8d0;font-size:.82rem;">Belum ada badge. <a href="{{ route('badge.create') }}" style="color:#667eea;">Buat sekarang</a>.</p>
                        @endif
                    </div>

                    {{-- User Select --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Karyawan Penerima</label>
                        <select name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror"
                                style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);color:#e0e0ff;border-radius:10px;">
                            <option value="" style="background:#1a1a2e;">-- Pilih Karyawan --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" style="background:#1a1a2e;" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn w-100 rounded-pill fw-bold" style="background:linear-gradient(90deg,#667eea,#f093fb);border:none;color:#fff;padding:.7rem;">
                        <i class="fas fa-paper-plane me-2"></i> Kirim Badge
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- RIWAYAT --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:4px;background:linear-gradient(90deg,#f5a623,#f093fb);"></div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#c8d0e0;"><i class="fas fa-history me-2" style="color:#f5a623;"></i> Riwayat Pengiriman</h6>

                @if($recent->isEmpty())
                <div class="text-center py-4" style="color:#a0a8d0;">
                    <div style="font-size:2rem;">📭</div>
                    <small>Belum ada badge yang dikirim.</small>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-borderless mb-0" style="color:#c8d0e0;font-size:.82rem;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,.08);">
                                <th style="color:#a0a8d0;font-weight:600;">Badge</th>
                                <th style="color:#a0a8d0;font-weight:600;">Penerima</th>
                                <th style="color:#a0a8d0;font-weight:600;">Dari</th>
                                <th style="color:#a0a8d0;font-weight:600;">Tanggal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent as $ub)
                            <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($ub->badge && $ub->badge->image)
                                        <img src="{{ s3_asset(true, null, $ub->badge->image) }}" style="width:28px;height:28px;object-fit:contain;" alt="">
                                        @else
                                        <span style="font-size:1.2rem;">🏅</span>
                                        @endif
                                        <span>{{ $ub->badge->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>{{ $ub->user->name ?? '-' }}</td>
                                <td style="color:#a0a8d0;">{{ $ub->givenBy->name ?? '-' }}</td>
                                <td style="color:#a0a8d0;">{{ $ub->created_at->format('d M Y') }}</td>
                                <td>
                                    @canAccess('revokeUserBadge','badges')
                                    <form action="{{ route('badge.revoke', $ub) }}" method="POST"
                                          onsubmit="return confirm('Cabut badge ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:.7rem;padding:2px 8px;border-radius:6px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endcanAccess
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $recent->links() }}</div>
                @endif
            </div>
        </div>
    </div>

</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $('.select2').select2({
        placeholder: 'Pilih',
        allowClear: true
    });
</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
.badge-radio:checked + .badge-card {
    border-color: #f093fb !important;
    background: rgba(240,147,251,.15) !important;
    box-shadow: 0 0 12px rgba(240,147,251,.4);
}
.badge-card:hover {
    border-color: rgba(102,126,234,.5) !important;
    background: rgba(102,126,234,.1) !important;
}
.form-control::placeholder { color:#606880; }
.form-control:focus { background:rgba(255,255,255,.1)!important;border-color:rgba(102,126,234,.6)!important;color:#e0e0ff!important;box-shadow:0 0 0 .2rem rgba(102,126,234,.25); }
select option { color: #000; }
     .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    .select2-selection__choice
    {
        background-color: #007bff !important;
        border: 1px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 1px solid #007bff !important;
    }
</style>
@stop
