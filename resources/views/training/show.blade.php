@extends('adminlte::page')

@section('content')
<div class="container py-3">
    <h2>Detail Pelatihan</h2>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="card mb-4">
        <div class="card-header">Informasi Pelatihan</div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nama Pelatihan:</strong> {{ $training->name }}</p>
                    <p><strong>Kemampuan yang Dikuasai:</strong>
                        <ul>
                        @foreach($training->skills_mastered as $skillId)
                            <li>{{ $skills[$skillId] ?? 'Not Found' }}</li>
                        @endforeach
                        </ul>
                    </p>
                    <p><strong>Tanggal Sertifikasi:</strong> {{ $training->certification_date }}</p>
                    <p><strong>Nomor Sertifikasi:</strong> {{ $training->certification_number }}</p>
                    <p><strong>Status:</strong> @switch($training->status)
                            @case('in review')
                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                @break
                            @case('complete')
                                <i class="fa fa-check" style="color: green;"></i> Complete
                                @break
                        @endswitch</p>
                    <p>
                        <strong>Sertifikasi File:</strong>
                        @if($training->certification_file)
                            <a href="{{ asset('storage/'.$training->certification_file) }}" target="_blank">View File</a>
                        @else
                            No file uploaded.
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <h5>Poin Pelatihan</h5>
                    @if(!$training->point && $training->user->approvement_user_id == Auth::user()->id)
                    @canAccess('addpoint','trainings')
                    <form action="{{ route('training.addPoint', $training->slug) }}" method="POST" class="p-3">
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
                    <p><strong>Point:</strong> {{ $training->point }}</p>
                    <p><strong>Approval:</strong> {{ $training->approvalUser ?   $training->approvalUser->name : ''   }}</p>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-between mt-4">
                @if(!$training->point)
                @canAccess('edit','trainings')
                <a href="{{ route('training.edit', $training->slug) }}" class="btn btn-warning">Edit Training</a>
                @endcanAccess
                @endif
                <a href="{{ route('training.index') }}" class="btn btn-secondary">Kembali Training</a>
            </div>
        </div>
    </div>

</div>
@endsection
