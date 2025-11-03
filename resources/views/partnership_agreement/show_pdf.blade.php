@extends('adminlte::page')

@section('content')
<div class="row">
    <div class="col-md-12 mt-3">
        @include('components.alert')
    </div>
    <div class="col-md-12">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('partnership-agreement.index') }}">Perjanjian Kemitraan</a></li>
            <li class="breadcrumb-item active">{{ $agreement->type->name}}</li>
        </ol>
    </div>
    @if($agreement->isPermission('messageReject') && $agreement->reason)
    <div class="col-md-12 mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Catatan :</strong> {{ $agreement->reason }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
    @endif
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
@canAccess('signature','partnership_agreements')
<div class="row">
    <div class="col-md-12 mt-3">
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title">Form Dokumen {{ $agreement->getTransalateSignature() ?? "" }}</h3>
                    @canAccess('share','partnership_agreements')
                    <div class="col-md-auto">
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-share">
                            <i class="fa fa-share-alt"></i> Share
                        </button>
                    </div>
                    @endcanAccess
                </div>

            </div>
            <form action="{{ route('partnership-agreement.signature', $agreement->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body row">
                    <div class="col-md-6 mb-2 mr-5">
                         <!-- KTP Upload Field -->
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
@endcanAccess
@endif
@endif

@if(view()->exists('partnership_agreement.pdf.' . $agreement->type->name_format))
@if($agreement->isPermission('approvement'))
@canAccess('approvement','partnership_agreements')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Approval Form</h3>
            </div>
            <div class="card-body">
                <!-- Display Signature Details -->
                <h4>Signatures</h4>
                <table class="table table-bordered table-striped">
                    <tbody>
                        @foreach($agreement->signature as $signature)
                            <tr>
                                <td colspan="2">{{ $signature->getTransalateSignature() }}</td>        
                            </tr>
                            <tr>
                                <!-- Displaying the KTP Image -->
                                <td>
                                    @if($signature->image_ktp)
                                        <img src="{{ s3_asset(true,10,$signature->image_ktp) }}" alt="KTP Image" width="250" class="img-thumbnail">
                                    @else
                                        <span>No KTP Image</span>
                                    @endif
                                </td>

                                <!-- Displaying the Signature Image -->
                                <td>
                                    @if($signature->signature)
                                        <img src="{{ s3_asset(true,10,$signature->signature) }}" alt="Signature Image" width="250" class="img-thumbnail">
                                    @else
                                        <span>No Signature Image</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Approval Form -->
                <form action="{{ route('partnership-agreement.approvement', $agreement->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="approval_status">Approval / Decline</label>
                        <select class="form-control" name="approval_status" id="approval_status" required>
                            <option value="approved">Approved</option>
                            <option value="rejected">Declined</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <textarea class="form-control" name="reason" id="reason" rows="4" ></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcanAccess
@endif
@endif

@if(view()->exists('partnership_agreement.pdf.' . $agreement->type->name_format))
<div class="d-flex align-items-center justify-content-center">
    <!-- Penambahan class text-center dan mt-3 -->
     @if($agreement->isPermission('edit'))
     @canAccess('edit','partnership_agreements')
        <a href="{{ route('partnership-agreement.edit',$agreement->id) }}" class="btn btn-warning mb-2 mr-2">
            <i class="fa fa-edit"></i>Edit
        </a>
    @endcanAccess
    @endif
    
    @if($agreement->isPermission('submit'))
        <form action="{{ route('partnership-agreement.submit', $agreement->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success mb-2 mr-2"
                onclick="return confirm('Apakah Anda yakin format dokumen ini sudah sesuai untuk di tanda tangani?')">
                <i class="fa fa-file-signature"></i> Submit</button>
        </form>
    @endif
    
    @if($agreement->isPermission('download'))
        <button type="button" id="downloadWorkOrder" class="btn btn-info mb-2 mr-2"><i class="fa fa-file-pdf"></i> Download</button>
    @endif
</div>
@endif

@canAccess('share','partnership_agreements')
<div class="modal fade" id="modal-share" tabindex="-1" role="dialog" aria-labelledby="modal-shareLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-shareLabel">Share Document</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="shareForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="sharePassword" name="password" placeholder="Enter password to protect link" required>
                    </div>
                    <div class="form-group">
                        <label for="shareLink">Document Link</label>
                        <input type="text" class="form-control" id="shareLink" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitShareForm">Share Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanAccess

@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>
<script>
$(document).ready(function() {
    // Trigger form submission via AJAX when "Share Link" button is clicked
    $('#shareForm').on('submit', function(e) {
        e.preventDefault();
        // Disable button to prevent multiple clicks
        $("").prop('disabled', true).text('Sharing...');

        // Collect form data
        var password = $('#sharePassword').val();
        var id = "{{ $agreement->id }}";  // Using the agreement's slug

        // Prepare data to send
        var formData = {
            password: password,
            _token: '{{ csrf_token() }}', // Include CSRF token
        };

        let url = "{{ route('partnership-agreement.share',':id') }}";
        url = url.replace(':id', id);

        $.ajax({
            url : url,
            method: 'GET',
            data: formData,
            success: function(response) {
                console.log(response);
                
                if (response.success) 
                {
                    $("#shareLink").val(response.url);

                    var copyText = document.getElementById('shareLink');
                    copyText.style.background = 'green';
                    copyText.style.color = 'white';
                    copyText.select();
                    document.execCommand('copy');
                    alert('Link copied to clipboard');

                } else {
                    alert('Error: ' + response.message);
                }
                // Re-enable the button
                $('#submitShareForm').prop('disabled', false).text('Share Link');
            },
            error: function(xhr, status, error) {
                // Handle errors
                alert('Something went wrong. Please try again.');
                $('#submitShareForm').prop('disabled', false).text('Share Link');
            }
        });
    });
});
</script>

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
    let name = "{{$agreement->number_result}}" + " {{ $agreement->type->name}}";
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
@section('css')
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
        font-size: 0.79rem;
    }

    .text-ads a, 
    .text-ads li, 
    .text-ads p, 
    .text-ads div, 
    .text-ads span, 
    .text-ads h1, 
    .text-ads h2, 
    .text-ads h3, 
    .text-ads h4, 
    .text-ads h5, 
    .text-ads h6 
    {
        font-size: 0.92rem;
    }
    .small-header
    {
        font-size: 1rem;
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
</style>
@stop