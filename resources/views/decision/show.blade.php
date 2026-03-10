@extends('adminlte::page')

@section('title', 'Analisa Keputusan')

@section('content_header')
    <h1>Analisa Keputusan</h1>
@stop

@section('content')
<div class="mt-3">
    <p>
        <a href="{{ route('home') }}" class="text-primary">Home</a> / <a href="{{ route('decision.index') }}" class="text-primary">Keputusan</a> / <span class="text-muted">{{ Str::limit($decision->question, 200) ?? '' }}</span>
    </p>
</div>
<div class="card">
    <div class="card-body" style="border: 1px solid #ccc; padding: 20px; background: #f8f9fa;">
        <div class="row">
            <div class="col-md-4">
                <h4 class="text-muted">Trust Score</h4>
                <h2><strong>{{ $decision->trust_score }}</strong></h2>
            </div>
            <div class="col-md-4">
                <h4 class="text-muted">Execution Score</h4>
                <h2><strong>{{ $decision->execution_score }}</strong></h2>
            </div>
            @if($decision->nominal)
            <div class="col-md-4">
                <h4 class="text-muted">Nilai / Nominal</h4>
                <h2><strong>Rp {{ number_format($decision->nominal, 0, ',', '.') }}</strong></h2>
            </div>
            @endif
        </div>
        
        @if(!$decision->is_approve)
        @canAccess('approvement','decisions')
        <form action="{{ route('decision.approvement', $decision->id) }}" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="approvalCheckbox" name="role[]" value="Manager" required>
                <label class="form-check-label" for="approvalCheckbox">Approvement</label>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Submit</button>
        </form>
        @endcanAccess
        @else
            <p class="text-success">This decision has been approved</p>
        @endif

        <hr>

        <div class="mt-3">
            <h5><strong>Pertanyaan:</strong></h5>
            <p>
                {{ $decision->question }}
            </p>
            <h5 class="mt-1"><strong>Analisis:</strong></h5>
            <p>
                {{ $decision->answer }}
            </p>
        </div>
    </div>
</div>

<div class="row mt-3">
    @if($decision->user)
    <div class="col-md-3">
        <div class="card">
            <div class="card-header" id="headingOne">
                <h2 class="mb-0">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Profil yang Bertanya
                    </button>
                </h2>
            </div>
            <div id="collapseOne" class="collapse" aria-labelledby="headingOne">
                <div class="card-body">
                    <ul>
                        <li>Nama: {{ $decision->user->name }}</li>
                        <li>Pendidikan: {{ strip_tags($decision->user->background) ?? '' }}</li>
                        <li>Pengalaman Kerja: {{ strip_tags($decision->user->experience) ?? '' }}</li>
                        <li>Keterampilan: {{ strip_tags($decision->user->skill) ?? '' }}</li>
                        <li>Pencapaian: {{ implode(', ', json_decode($decision->user->achievement) ?? []) }}</li>
                        <li>Kegagalan: {{ implode(', ', json_decode($decision->user->failure) ?? []) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($decision->userResponsible)
    <div class="col-md-3">
        <div class="card">
            <div class="card-header" id="headingThree">
                <h2 class="mb-0">
                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        Profil yang Bertanggung Jawab (Pelaku)
                    </button>
                </h2>
            </div>
            <div id="collapseThree" class="collapse" aria-labelledby="headingThree">
                <div class="card-body">
                    <ul>
                        <li>Nama: {{ $decision->userResponsible->name }}</li>
                        <li>Pendidikan: {{ strip_tags($decision->userResponsible->background) ?? '' }}</li>
                        <li>Pengalaman Kerja: {{ strip_tags($decision->userResponsible->experience) ?? '' }}</li>
                        <li>Keterampilan: {{ strip_tags($decision->userResponsible->skill) ?? '' }}</li>
                        <li>Pencapaian: {{ implode(', ', json_decode($decision->userResponsible->achievement) ?? []) }}</li>
                        <li>Kegagalan: {{ implode(', ', json_decode($decision->userResponsible->failure) ?? []) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($decision->userAccount)
    <div class="col-md-3">
        <div class="card">
            <div class="card-header" id="headingTwo">
                <h2 class="mb-0">
                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Profil yang Bertanggung Jawab (PIC)
                    </button>
                </h2>
            </div>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo">
                <div class="card-body">
                    <ul>
                        <li>Nama: {{ $decision->userAccount->name }}</li>
                        <li>Pendidikan: {{ strip_tags($decision->userAccount->background) ?? '' }}</li>
                        <li>Pengalaman Kerja: {{ strip_tags($decision->userAccount->experience) ?? '' }}</li>
                        <li>Keterampilan: {{ strip_tags($decision->userAccount->skill) ?? '' }}</li>
                        <li>Pencapaian: {{ implode(', ', json_decode($decision->userAccount->achievement) ?? []) }}</li>
                        <li>Kegagalan: {{ implode(', ', json_decode($decision->userAccount->failure) ?? []) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($decision->userConsult)
    <div class="col-md-3">
        <div class="card">
            <div class="card-header" id="headingFour">
                <h2 class="mb-0">
                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        Profil Konsultan
                    </button>
                </h2>
            </div>
            <div id="collapseFour" class="collapse" aria-labelledby="headingFour">
                <div class="card-body">
                    <ul>
                        <li>Nama: {{ $decision->userConsult->name }}</li>
                        <li>Pendidikan: {{ strip_tags($decision->userConsult->background) ?? '' }}</li>
                        <li>Pengalaman Kerja: {{ strip_tags($decision->userConsult->experience) ?? '' }}</li>
                        <li>Keterampilan: {{ strip_tags($decision->userConsult->skill) ?? '' }}</li>
                        <li>Pencapaian: {{ implode(', ', json_decode($decision->userConsult->achievement) ?? []) }}</li>
                        <li>Kegagalan: {{ implode(', ', json_decode($decision->userConsult->failure) ?? []) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($decision->consult_vendor)
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-header bg-warning" id="headingFive">
                <h2 class="mb-0">
                    <button class="btn btn-link text-dark" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="true" aria-controls="collapseFive">
                        <i class="fas fa-building mr-1"></i> Vendor Eksternal
                    </button>
                </h2>
            </div>
            <div id="collapseFive" class="collapse show" aria-labelledby="headingFive">
                <div class="card-body">
                    <ul>
                        <li><strong>Nama Vendor:</strong> {{ $decision->consult_vendor }}</li>
                        @if($decision->nominal)
                        <li><strong>Nilai Kontrak:</strong> Rp {{ number_format($decision->nominal, 0, ',', '.') }}</li>
                        @endif
                    </ul>
                    <div class="alert alert-warning py-1 px-2 mt-2 mb-0" style="font-size:0.85em;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Nilai melebihi threshold — pastikan due diligence vendor sudah dilakukan.
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@stop
@section('js')
<!-- ✅ Pastikan jQuery Dimuat Terlebih Dahulu -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@stop