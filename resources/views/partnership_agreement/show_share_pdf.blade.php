@extends('adminlte::master')

@section('body')
<div class="row pr-5 pl-5">
    <div class="col-md-12 mt-3">
        @include('components.alert')
    </div>

    @if(view()->exists('partnership_agreement.pdf.' . $agreement->type->name_format))
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
                         <!-- KTP Upload Field -->
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Enter password to protect link" require>
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
                    <!-- Signature Field -->
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
                    <!-- Compression Options -->
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
<script>
    // Signature Pad Initialization
    let signaturePad = null;
    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('signaturePad');
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

    // KTP Upload Handling
    document.getElementById('ktpUpload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('ktpPreviewImg').src = e.target.result;
                document.getElementById('ktpPreview').classList.remove('d-none');
            }
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

    // Form Submission
</script>
<script>
$(document).ready(function() {
    updateCustomerField();

    $('.select2').select2({
        width: '100%',
        placeholder: 'Pilih Quote'
    });

    $("#downloadWorkOrder").click(function(e) {
        e.preventDefault();
        prinsts();

    });

    $(".select2").on("change", updateCustomerField);
});


function updateCustomerField() {
    // Mendapatkan nilai dari atribut data-customer
    var customerName = $(".select2").find("option:selected").data("customer");

    // Menampilkan nilai tersebut di elemen dengan id "customer"
    $("#customer").val(customerName);
}

function prinsts() {
    let name = " $nomorAgreementLetter " + "_surat_perjanjian";
    let printContents = document.getElementById("printThis").innerHTML;
    let originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;

    window.addEventListener("beforeprint", (event) => {
        document.title = name;
    });

    window.print();
    document.body.innerHTML = originalContents;
}
</script>
@stop
@section('adminlte_css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .img-signature
    {
        background-color: transparent !important; 
        border: 0px solid #dee2e6 !important;
        box-shadow: 0px 0px 0px 0px rgba(0,0,0,0.0) !important;       
        max-height: 100px !important; 
    }
    .signature-container {
        width: fit-content;
    }
    .signature-canvas {
        /* width: 100%; */
        /* height: 200px; */
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background-color: white;
        touch-action: none;
    }
    .custom-file-label::after {
        content: "Browse";
    }
    #ktpPreviewImg {
        max-height: 200px;
    }
</style>
<style>
   .small-text 
    {
        text-align: justify;
        font-size: 0.6rem;
    }
    .small-header
    {
        font-size: 0.7rem;
        font-weight: bold;
    }
@media print {
    #printItem {
        margin-left: 50px;
        margin-right: 50px;
    }
}

body {
    font-family: Arial;
    /* font-size : 12px; */
    /* padding: 20px; */
    /* background-color: #f4f4f4; */
}

.container {
    /* background-color: #fff; */
    padding: 10px;
    border-radius: 5px;
}

.select2-selection__rendered {
    line-height: 31px !important;
}

.select2-container .select2-selection--single {
    height: 35px !important;
}

.select2-selection__arrow {
    height: 34px !important;
}

hr {
    border: 1px solid black;
    border-radius: 5px;
}

.select2-selection__rendered {
    line-height: 31px !important;
}

.select2-container .select2-selection--single {
    height: 35px !important;
}

.select2-selection__arrow {
    height: 34px !important;
}

/* li */
.margin {
    margin-bottom: 15px;
}

.noMargin {
    margin-bottom: 0px;
}

.scrollable {
    width: 100%;
    height: 650px;
    overflow: auto;
    border: 1px solid #ccc;
}
.main-footer 
{
    display: none;
}mi
</style>
@stop