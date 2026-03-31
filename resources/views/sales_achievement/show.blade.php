@extends('adminlte::page')

@section('content')
<div class="container py-3">
    <h2>Detail Capaian Penjualan</h2>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Periode Capaian Penjualan:</strong>
                        {{ $salesAchievement->periodShow }}
                    </div>
                    <div class="mb-3">
                        <strong>Capaian Penjualan:</strong>
                        Rp {{ number_format($salesAchievement->sales_amount, 2, ',', '.') }}
                    </div>
                    <div class="mb-3">
                        <strong>Jumlah Presentasi:</strong>
                        {{ $salesAchievement->total_presentations }}
                    </div>
                    <div class="mb-3">
                        <strong>Jumlah Penawaran Diterbitkan:</strong>
                        {{ $salesAchievement->total_offers_issued }}
                    </div>
                    <div class="mb-3">
                        <strong>Jumlah Kunjungan Pelanggan:</strong>
                        {{ $salesAchievement->customer_visits }}
                    </div>
                    <div class="mb-3">
                        <strong>Jumlah Pelanggan Daftar:</strong>
                        {{ $salesAchievement->registered_customers }}
                    </div>
                    <div class="mb-3">
                        <strong>Jumlah Pelanggan Aktif:</strong>
                        {{ $salesAchievement->active_customers }}
                    </div>
                    <div class="mb-3">
                        <strong>Poin:</strong>
                        {{ $salesAchievement->points ?? 'Belum ada poin' }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        @switch($salesAchievement->status)
                            @case('in review')
                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                @break
                            @case('complete')
                                <i class="fa fa-check" style="color: green;"></i> Complete
                                @break
                        @endswitch
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Poin Pencapaian Penjualan</h5>
                    @if(!$salesAchievement->points && $salesAchievement->user->approvement_user_id == Auth::user()->id)
                    @canAccess('addpoint','sales_achievements')
                    <form action="{{ route('sales_achievement.addPoint', $salesAchievement->slug) }}" method="POST" class="p-3">
                        @csrf
                        @method('put')
                        <div class="mb-3">
                            <label for="points" class="form-label">Poin</label>
                            <input type="number" class="form-control" id="points" name="point" required>
                            @error('point')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success">Simpan Poin</button>
                    </form>
                    @endcanAccess
                    @else
                    <p><strong>Point:</strong> {{ $salesAchievement->points }}</p>
                    <p><strong>Tanggal Point:</strong> {{ $salesAchievement->attempt_point_date ? $salesAchievement->attempt_point_date->format('d M Y H:i') : '-' }}</p>
                    <p><strong>Approval:</strong> {{ $salesAchievement->approvalUser ?   $salesAchievement->approvalUser->name : ''   }}</p>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-between mt-4">
                @if(!$salesAchievement->approved)
                @canAccess('edit','sales_achievements')
                <a href="{{ route('sales_achievement.edit', $salesAchievement->id) }}" class="btn btn-primary">Edit</a>
                @endcanAccess
                @endif
                <a href="{{ route('sales_achievement.index') }}" class="btn btn-secondary">Kembali Training</a>
            </div>
        </div>
    </div>
</div>
@endsection
