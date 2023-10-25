@extends('adminlte::page')

@section('content')
<div class="container mt-5">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Surat Perjanjian Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Surat Perjanjian Berhasil Diperbarui</div>
        @endif
    </div>
    <div class="card" id="printThis">
        <div class="card-body">
            @if(@$agreementLetter)
            <form method="post" action="{{ route('agreement-letter.update',$agreementLetter) }}">
                @method('put')
            @else
            <form method="post" action="{{ route('agreement-letter.store') }}">
            @endif
                @csrf
                <div class="form-group row">
                    <div class="col-md-6">
                        <h2>Surat Perjanjian</h2>
                        <div class="mt-5">No Surat Perjanjain: {{ $nomorAgreementLetter ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                            <div class="col-sm-4">
                                <span class="form-control-plaintext">{{ @$agreementLetter->date ?? '' }}</span>

                            </div>
                        </div>
                        <div class="row mt-2">
                            <label class="col-sm-8 col-form-label text-right">Finance:</label>
                            <div class="col-sm-4">
                                <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="form-group row">
                    <label for="quote" class="col-sm-2 col-form-label">Pilih No. Quote:</label>
                    <div class="col-sm-2">
                        <span class="form-control-plaintext">{{ $agreementLetter->quote ? $agreementLetter->quote->number_result :  '' }}</span>
                    </div>
                </div>
        
                <div class="form-group row">
                    <label for="customer" class="col-sm-2 col-form-label">Customer:</label>
                    <div class="col-sm-5">
                        @if($agreementLetter->quote)
                        @if($agreementLetter->quote->customer)
                        <span class="form-control-plaintext">{{ $agreementLetter->quote->customer? $agreementLetter->quote->customer->name : '' }}</span>
                        @endif
                        @endif
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="pembayaran">Klausul Termin Pembayaran</label>
                    <span class="form-control-plaintext">{{ $agreementLetter->payment_term ?? '' }}</span>
                </div>
        
                <div class="form-group">
                    <label for="periode">Klausul Periode Perjanjian</label>
                    <span class="form-control-plaintext">{{ $agreementLetter->period_term ?? '' }}</span>
                </div>
        
                <div class="form-group">
                    <label for="tambahan">Klausul Tambahan Lain</label>
                    <span class="form-control-plaintext">{{ $agreementLetter->other_term ?? '' }}</span>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-12 text-center mt-3"> <!-- Penambahan class text-center dan mt-3 -->
        <a href="{{ route('agreement-letter.edit',$agreementLetter->slug) }}" class="btn btn-primary"><i class="fa fa-edit"></i>Edit</a>
        <button type="button" id="downloadWorkOrder" class="btn btn-success"><i class="fa fa-file-pdf"></i> {{__('Download')}}</button>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        updateCustomerField();

        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Quote'
        });

        $("#downloadWorkOrder").click(function (e) 
        { 
            e.preventDefault();
            prinsts();
            
        });

        $(".select2").on("change", updateCustomerField);
    });


    function updateCustomerField() 
    {
        // Mendapatkan nilai dari atribut data-customer
        var customerName = $(".select2").find("option:selected").data("customer");
        
        // Menampilkan nilai tersebut di elemen dengan id "customer"
        $("#customer").val(customerName);
    }
    function prinsts() 
    {
        let name = "{{ $nomorAgreementLetter }}"+"_surat_perjanjian";
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
   body 
   {
        font-family: Arial, sans-serif;
        /* padding: 20px; */
        background-color: #f4f4f4;
    }
    .select2-selection__rendered 
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single 
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
</style>
@stop
