@extends('adminlte::page')

@section('content')
<div class="container py-3">
    <h2>Detail Hak Kekayaan Intelektual</h2>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h4 class="card-title">{{ $ipRight->name }}</h4>
                    <p class="card-text"><strong>Tanggal Paten:</strong> {{ \Carbon\Carbon::parse($ipRight->patent_date)->format('d-m-Y')  }}</p>
                    <p class="card-text"><strong>Nomor Paten:</strong> {{ $ipRight->patent_number }}</p>
                    <p class="card-text"><strong>Deskripsi:</strong> {!! $ipRight->description !!}</p>
                    <p class="card-text"><strong>Status:</strong> {{ $ipRight->status }}</p>
                    <p class="card-text"><strong>Diapprove oleh:</strong> {{ $ipRight->approver->name ?? 'Belum diapprove' }}</p>

                    @if($ipRight->file_path)
                    <div class="mb-3">
                        <a href="{{ asset('storage/' . $ipRight->file_path) }}" target="_blank" class="btn btn-primary">Lihat Dokumen</a>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <h5>Poin Hak Kekayaan Intelektual</h5>
                    @if(!$ipRight->point && $ipRight->user->approvement_user_id == Auth::user()->id)
                    @canAccess('addpoint','ip_rights')
                    <form action="{{ route('ipright.addPoint', $ipRight->slug) }}" method="POST" class="p-3">
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
                    <p><strong>Point:</strong> {{ $ipRight->point }}</p>
                    <p><strong>Approval:</strong> {{ $ipRight->approvalUser ?   $ipRight->approvalUser->name : ''   }}</p>
                    @endif

                </div>
            </div>
            <div class="d-flex justify-content-between mt-4">
                @if(!$ipRight->point)
                @canAccess('edit','ip_rights')
                <a href="{{ route('ipright.edit', $ipRight->id) }}" class="btn btn-warning">Edit</a>
                @endcanAccess
                @endif
                <a href="{{ route('ipright.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
