@extends('adminlte::master')

@section('body')

@if(isset($needsPassword) && $needsPassword)
{{-- PASSWORD GATE: Konten PDF tidak di-render sama sekali di HTML --}}
<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh; background-color: #f4f6f9;">
    <div class="card shadow-lg" style="max-width: 440px; width: 100%; margin: 20px;">
        <div class="card-body p-4 text-center">
            <div class="mb-3">
                <i class="fas fa-lock fa-3x text-primary"></i>
            </div>
            <h5 class="font-weight-bold mb-1">Dokumen Dilindungi Password</h5>
            <p class="text-muted small mb-3">Masukkan password untuk mengakses dokumen ini.</p>

            <div id="passwordError" class="alert alert-danger d-none py-2 mb-3" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span id="passwordErrorMsg">Password salah.</span>
            </div>

            <div class="form-group text-left">
                <label class="font-weight-bold">Password</label>
                <input type="password" id="sharePasswordInput" class="form-control form-control-lg" placeholder="Masukkan password..." autofocus>
            </div>

            @php $remaining = 3 - (isset($attempts) ? $attempts : 0); @endphp
            <div class="text-muted small mb-3">
                Sisa percobaan: <strong id="remainingAttempts">{{ $remaining }}</strong>x
            </div>

            <button id="verifyPasswordBtn" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-unlock"></i> Buka Dokumen
            </button>

            <hr class="mt-4">
            <p class="text-muted small mb-0">
                <i class="fas fa-shield-alt"></i>
                Dokumen dilindungi oleh sistem keamanan Thrive.
            </p>
        </div>
    </div>
</div>
@else
{{-- KONTEN PENUH: hanya di-render setelah autentikasi (session valid) --}}

<div class="row pr-5 pl-5">
    <div class="col-md-12 mt-3">
        @include('components.alert')
    </div>

    @if(view()->exists('partnership_agreement.pdf.' . $agreement->type->name_format))
    @if($agreement->isPermission('download'))
    <div class="col-md-12 mb-2 d-flex justify-content-end">
        <button onclick="prinsts()" class="btn btn-primary">
            <i class="fas fa-download"></i> Download / Print
        </button>
    </div>
    @endif
    <div class="card scrollable" id="printThis">
        @include('partnership_agreement.pdf.' . $agreement->type->name_format, ['agreement' => $agreement])
    </div>
    @else
    <div class="d-flex justify-content-center">
        <div class="card">
            <div class="card-body text-center">
                <h5><i class="fa fa-exclamation-circle"></i> Tidak Ada Template Yang Tersedia</h5>
            </div>
        </div>
    </div>
    @endif
</div>

@if(view()->exists('partnership_agreement.pdf.' . $agreement->type->name_format))
@if($agreement->isPermission('signature'))
<div class="row pr-5 pl-5">
    <div class="col-md-12 mt-3">
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title">Form Dokumen {{ $agreement->getTransalateSignature() ?? "" }}</h3>
                </div>
            </div>
            <form action="{{ route('partnership-agreement.signatureShare', $agreement->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body row">
                    <div class="col-md-6 mb-2 mr-5">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Masukkan password dokumen" required>
                        </div>
                        <div class="form-group">
                            <label>Upload KTP</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="ktpUpload" name="ktp" accept="image/*">
                                <label class="custom-file-label" for="ktpUpload">Pilih file...</label>
                            </div>
                            <small class="form-text text-muted">Format: JPG/PNG (Maks. 2MB)</small>

                            <div id="ktpPreview" class="mt-3 d-none">
                                <p class="mb-1">Preview KTP:</p>
                                <img id="ktpPreviewImg" class="img-thumbnail" style="max-width: 300px;">
                                <button type="button" class="btn btn-sm btn-danger ml-2" onclick="removeKTP()">
                                    <i class="fas fa-times"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanda Tangan</label>
                            <div class="signature-container mb-3">
                                <div class="border rounded p-2" style="background-color: #f8f9fa;">
                                    <canvas id="signaturePad" class="signature-canvas"></canvas>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearSignature()">
                                        <i class="fas fa-eraser"></i> Hapus
                                    </button>
                                </div>
                                <input type="hidden" name="signature" id="signatureInput">
                            </div>
                            <div id="signaturePreview" class="d-none mt-2">
                                <p class="mb-1">Preview:</p>
                                <img id="signaturePreviewImg" class="border p-1" style="max-width: 300px;">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="compressCheck" checked>
                            <label class="custom-control-label" for="compressCheck">
                                Kompresi dokumen (Optimalkan ukuran file)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Dokumen
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="clearAll()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endif

@endif {{-- end needsPassword --}}

@stop

@section('adminlte_js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>

@if(isset($needsPassword) && $needsPassword)
<script>
$(document).ready(function() {
    const verifyUrl = "{{ route('partnership-agreement.verifySharePassword', $agreement->id) }}?token={{ $token ?? '' }}";

    function doVerify() {
        const password = $('#sharePasswordInput').val();
        if (!password) {
            $('#passwordErrorMsg').text('Password tidak boleh kosong.');
            $('#passwordError').removeClass('d-none');
            return;
        }

        $('#verifyPasswordBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memeriksa...');
        $('#passwordError').addClass('d-none');

        $.ajax({
            url: verifyUrl,
            method: 'POST',
            data: { password: password, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    $('#verifyPasswordBtn').html('<i class="fas fa-check"></i> Berhasil! Membuka dokumen...');
                    window.location.reload();
                } else if (response.blocked) {
                    $('body').html('<div class="d-flex justify-content-center align-items-center" style="min-height:100vh;background:#f4f6f9;"><div class="card shadow p-5 text-center" style="max-width:440px;width:100%;"><i class="fas fa-lock fa-4x text-danger mb-3"></i><h4 class="text-danger font-weight-bold">Akses Diblokir</h4><p class="text-muted">Anda telah melebihi batas maksimal percobaan password (3x). Hubungi pengirim dokumen untuk mendapatkan bantuan.</p></div></div>');
                } else {
                    $('#remainingAttempts').text(response.remaining);
                    $('#passwordErrorMsg').text('Password salah. Sisa percobaan: ' + response.remaining + 'x');
                    $('#passwordError').removeClass('d-none');
                    $('#sharePasswordInput').val('').focus();
                    $('#verifyPasswordBtn').prop('disabled', false).html('<i class="fas fa-unlock"></i> Buka Dokumen');
                }
            },
            error: function() {
                $('#passwordErrorMsg').text('Terjadi kesalahan. Silakan coba lagi.');
                $('#passwordError').removeClass('d-none');
                $('#verifyPasswordBtn').prop('disabled', false).html('<i class="fas fa-unlock"></i> Buka Dokumen');
            }
        });
    }

    $('#verifyPasswordBtn').on('click', doVerify);
    $('#sharePasswordInput').on('keypress', function(e) {
        if (e.which === 13) doVerify();
    });
});
</script>
@else
<script>
    let signaturePad = null;
    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('signaturePad');
        if (!canvas) return;
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        canvas.addEventListener('mouseup', updateSignaturePreview);
        canvas.addEventListener('touchend', updateSignaturePreview);
    });

    function updateSignaturePreview() {
        const dataURL = signaturePad.toDataURL();
        document.getElementById('signaturePreviewImg').src = dataURL;
        document.getElementById('signatureInput').value = dataURL;
        document.getElementById('signaturePreview').classList.remove('d-none');
    }

    function clearSignature() {
        signaturePad.clear();
        document.getElementById('signaturePreview').classList.add('d-none');
        document.getElementById('signatureInput').value = '';
    }

    document.getElementById('ktpUpload') && document.getElementById('ktpUpload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('ktpPreviewImg').src = e.target.result;
                document.getElementById('ktpPreview').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
            document.querySelector('.custom-file-label').textContent = file.name;
        }
    });

    function removeKTP() {
        document.getElementById('ktpUpload').value = '';
        document.getElementById('ktpPreview').classList.add('d-none');
        document.querySelector('.custom-file-label').textContent = 'Pilih file...';
    }

    function clearAll() {
        clearSignature();
        removeKTP();
        document.getElementById('compressCheck').checked = true;
    }
</script>

<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        placeholder: 'Pilih Quote'
    });
});

function prinsts() {
    let name = "surat_perjanjian";
    let printContents = document.getElementById("printThis").innerHTML;
    let originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.addEventListener("beforeprint", function() {
        document.title = name;
    });
    window.print();
    document.body.innerHTML = originalContents;
}
</script>
@endif
@stop

@section('adminlte_css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .img-signature {
        background-color: transparent !important;
        border: 0px solid #dee2e6 !important;
        box-shadow: 0px 0px 0px 0px rgba(0,0,0,0.0) !important;
        max-height: 100px !important;
    }
    .signature-container { width: fit-content; }
    .signature-canvas {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background-color: white;
        touch-action: none;
    }
    .custom-file-label::after { content: "Browse"; }
    #ktpPreviewImg { max-height: 200px; }
    .small-text { text-align: justify; font-size: 0.79rem; }
    .text-ads a, .text-ads li, .text-ads p, .text-ads div,
    .text-ads span, .text-ads h1, .text-ads h2, .text-ads h3,
    .text-ads h4, .text-ads h5, .text-ads h6 { font-size: 0.92rem; }
    .small-header { font-size: 0.7rem; font-weight: bold; }
    .scrollable { width: 100%; height: 650px; overflow: auto; border: 1px solid #ccc; }
    .main-footer { display: none; }
    @media print {
        #printItem { margin-left: 50px; margin-right: 50px; }
    }
    body { font-family: Arial; }
    hr { border: 1px solid black; border-radius: 5px; }
    .select2-selection__rendered { line-height: 31px !important; }
    .select2-container .select2-selection--single { height: 35px !important; }
    .select2-selection__arrow { height: 34px !important; }
    .noMargin { margin-bottom: 0px; }
</style>
@stop
