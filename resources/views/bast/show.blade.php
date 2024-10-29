@extends('adminlte::page')

@section('title', 'Detail BAST')

@section('content')
<div class="row mt-3">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Detail BAST</h3>
            </div>
            <div class="card-body">
                <!-- Informasi BAST -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>No. BAST: {{ $bast->number_result ?? '-' }}</h5>
                        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($bast->created_at)->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>No. Purchase Order:</strong> {{ $bast->number_purchase ?? '-' }}</p>
                        <p><strong>Penanggung Jawab:</strong> {{ $bast->pic ?? '-' }}</p>
                        <p><strong>Perusahaan:</strong> {{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '-' }}</p>
                    </div>
                </div>
    
                <!-- Preview PDF -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="mb-3">Preview PDF BAST</h5>
                        <iframe src="{{ route('bast.showPdf', $bast->slug) }}" 
                                style="width: 100%; height: 600px; border: 1px solid #ccc;" 
                                frameborder="0">
                        </iframe>
                    </div>
                </div>
    
                <!-- Action Buttons -->
                <div class="text-right mt-4">
                    <a href="{{ route('bast.index') }}" class="btn btn-secondary">Kembali</a>
                    <a href="{{ route('bast.showPdf', $bast->slug) }}" class="btn btn-primary" target="_blank">
                        Unduh PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <!-- Email Form -->
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Kirim Email</h3>
            </div>
            <div class="card-body">
    
                    @csrf
                    <div class="form-group">
                        <label for="to">To:</label>
                        <select name="to[]" class="form-control select2" multiple="multiple" required>
                            @if($bast->workOrder)
                                <option value="{{ $bast->workOrder->quote->customer->email }}" selected>{{ $bast->workOrder->quote->customer->email }}</option>
                            @endif
                            @if(old('to'))
                                @foreach(old('to') as $email)
                                    <option value="{{ $email }}" selected>{{ $email }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cc">CC:</label>
                        <select name="cc[]" class="form-control select2" multiple="multiple">
                        <option value="{{ Auth::user()->email }}" selected>{{ Auth::user()->email }}</option>
                            @if(old('cc'))
                                @foreach(old('cc') as $email)
                                    <option value="{{ $email }}" selected>{{ $email }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" name="subject" class="form-control" placeholder="Masukkan subject email">
                    </div>
                    <div class="form-group">
                        <label for="content">Isi:</label>
                        <input class="thriveEditor form-control" id="description_content" data-ids="content" name="content" rows="3" placeholder="yang akan dicetak di perjanjian"/>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Email</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            tags: true,
            placeholder: 'Pilih email',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection

@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 6px;
        height: auto;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
        padding: 3px 10px;
    }
    .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
    }
    .select2-selection__arrow {
        height: 36px !important;
    }
    .ql-container 
    {
        min-height: 150px;
        height: auto;
    }
</style>
@endsection
