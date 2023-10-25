@extends('adminlte::page')
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">BAST Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">BAST Berhasil Diperbarui</div>
    @endif
</div>
<div class="containe mt-5">
    <div class="card" id="printThis">
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-6">
                    <h2>BAST</h2>
                    <div class="mt-5">No BAST: {{ $nomorBast ?? '' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                        <div class="col-sm-4">
                            <span class="form-control-plaintext">{{ @$bast->date ?? '' }}</span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <label class="col-sm-8 col-form-label text-right">Sales:</label>
                        <div class="col-sm-4">
                            <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="pilihSPK" class="form-label">Pilih SPK</label>
                <span class="form-control-plaintext">{{ $bast->workOrder ? $bast->workOrder->number_result : '' }}</span>
            </div>
            <div class="form-group">
                <label for="pilihDataProyek" class="form-label">Pilih Data Proyek</label>
                <span class="form-control-plaintext">{{ $bast->project ? $bast->project->title : '' }}</span>
            </div>
            <hr>
            <div class="form-group">
                <label for="purchaseOrder" class="form-label">No. Purchase Order</label>
                <span class="form-control-plaintext">{{ @$bast->number_purchase ?? '' }}</span>
            </div>
            <div class="form-group">
                <label for="penanggungJawab" class="form-label">Penanggung Jawab</label>
                <span class="form-control-plaintext">{{ @$bast->pic ?? '' }}</span>
            </div>
            <hr>
        </div>
    </div>
    <div class="col-md-12 text-center mt-3"> <!-- Penambahan class text-center dan mt-3 -->
        <a href="{{ route('bast.edit',$bast->slug) }}" class="btn btn-primary"><i class="fa fa-edit"></i>Edit</a>
        <button type="button" id="downloadBast" class="btn btn-success"><i class="fa fa-file-pdf"></i> {{__('Download')}}</button>
    </div>
</div>
@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () 
    {
        $("#downloadBast").click(function (e) 
        { 
            e.preventDefault();
            prinsts();
            
        });

        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Quote'
        });
    });

    function prinsts() 
    {
        let name = "{{ $nomorBast }}"+"_bast";;
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
    .container {
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
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
    hr {
        border: 1px solid black;
        border-radius: 5px;
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

