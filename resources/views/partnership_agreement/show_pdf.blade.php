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

    @if(view()->exists('partnership_agreement.pdf.' . $agreement->type->name_format))
    <div class="col-md-12 text-center mt-3">
        <!-- Penambahan class text-center dan mt-3 -->
         @canAccess('edit','partnership_agreements')
        <a href="{{ route('partnership-agreement.edit',$agreement->id) }}" class="btn btn-primary"><i
                class="fa fa-edit"></i>Edit</a>
        @endcanAccess
        <button type="button" id="downloadWorkOrder" class="btn btn-success"><i class="fa fa-file-pdf"></i> Download</button>
    </div>
    @endif
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
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
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

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
</style>
@stop