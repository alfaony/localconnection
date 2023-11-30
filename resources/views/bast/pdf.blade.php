
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
<div class="container mt-5">
    <div class="card" id="printThis">
        <div class="card-body" id="printItem">
            <div class="row">
                <div class="col-md-12 text-center">
                <h1>Berita Acara Serah Terima</h1>
                <p>No. {{ $bast->number ?? '' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                <table class="table table-bordered">
                    <tr>
                    <th>Nomor</th>
                    <td>{{ $bast->number }}</td>
                    </tr>
                    <tr>
                    <th>Tanggal</th>
                    <td>{{ $today  ?? ''}}</td>
                    </tr>
                    <tr>
                    <th>No. Purchase Order</th>
                    <td>{{ $bast->number_purchase ?? '' }}</td>
                    </tr>
                    <tr>
                    <th>Penanggung Jawab</th>
                    <td>
                        {{ $bast->pic ?? '' }}
                    </td>
                    </tr>
                    <tr>
                    <th>Perusahaan</th>
                    <td>{{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</td>
                    </tr>
                </table>
                </div>
                <div class="col-md-12">
                <p>Bersamaan dengan surat pernyataan ini, pekerjaan dengan nomor purchase order diatas dengan rincian pekerjaan:</p>
                <p><strong>{{ $bast->project ? $bast->project->title : '' }}</strong></p>
                <p>Telah diselesaikan dengan baik. Laporan bisa di unduh di link berikut ini</p>
                <ul>
                    @if($bast->project)
                    @if($bast->project->reportProject->reportProjectDetail)
                    @php $detail = $bast->project->reportProject->reportProjectDetail; @endphp
                    @foreach($detail as $a)
                    <li>
                        {{ $a->name .' - ' }} <a href="{{ Storage::url('reports/' . $a->file) }}" class="text-primary" download title="{{ $a->file }}"> {{ $a->file }} </a>
                    </li>
                    @endforeach
                    @endif
                    @endif
                </ul>
                <ul>
                </ul>
                </div>
            </div>
            <div class="mt-5">
                <div class="row">
                    <div class="offset-1 col-3">
                        <span style="margin-bottom: 0;">TTD</span>
                    </div>
                    <div class="offset-5 text-left">
                        <p>Diterima,</p>
                    </div>
                    
                    <div class="col-11 text-right mt-5 mb-5" id="space">

                    </div>

                    <div class="offset-1 col-3">
                        {{ $company['director'] ?? '' }}
                    </div>
                    <div class="offset-5 text-left">
                        <p class="noMargin">{{ $bast->workOrder ? $bast->workOrder->quote->customer->pic : '' }}</p>
                    </div>
                    <div class="offset-9 text-left">
                        <p>{{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</p>
                    </div>
                </div>
            </div>
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
    @media print 
    {
        #printItem
        {
            margin-left : 50px;
            margin-right : 50px;
        }
    }
   body 
   {
        font-family: Arial, sans-serif;
        /* padding: 20px; */
        /* background-color: #f4f4f4; */
    }
    .container {
        /* background-color: #fff; */
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
    .noMargin
    {
        margin-bottom:0px;
    }
</style>
@stop