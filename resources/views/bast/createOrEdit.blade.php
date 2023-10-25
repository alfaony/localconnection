@extends('adminlte::page')
@section('content')
<div class="containe mt-3">
    <div class="card">
        <div class="card-body">
            @if(@$bast)
            <form method="post" action="{{ route('bast.update',$bast) }}">
                @method('put')
            @else
            <form method="post" action="{{ route('bast.store') }}">
            @endif
                @csrf
                <div class="form-group row">
                    <div class="col-md-6">
                        <h2>BAST</h2>
                        <div class="mt-5">No BAST: {{ $nomorBast ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                            <div class="col-sm-4">
                                <input type="date" name="date" class="form-control" id="date" value="{{ old('date') ?? @$bast->date }}" required>
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
                    <select class="form-control select2" name="work_order" id="pilihSPK" required>
                        <option value="" selected disabled>Pilih</option>
                        @foreach($workOrder as $a)
                        <option value="{{ $a->id }}" {{ @$bast->work_order_id == $a->id ? 'selected' : '' }}>{{ $a->number_result }}</option>
                        @endforeach
                        <!-- Other options can be added here -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="pilihDataProyek" class="form-label">Pilih Data Proyek</label>
                    <select class="form-control select2" name="project" id="pilihDataProyek" required>
                        <option value="" disabled selected>Pilih</option>
                        @foreach($project as $a)
                        <option value="{{ $a->id }}" {{ @$bast->project_id == $a->id ? 'selected' : '' }}>{{ $a->title }}</option>
                        @endforeach
                        <!-- Other options can be added here -->
                    </select>
                </div>
                <hr>
                <div class="form-group">
                    <label for="purchaseOrder" class="form-label">No. Purchase Order</label>
                    <input type="text" class="form-control" name="number_purchase" id="purchaseOrder" placeholder="PO 048392003" value="{{ old('number_purchase') ?? @$bast->number_purchase  }}" required>
                </div>
                <div class="form-group">
                    <label for="penanggungJawab" class="form-label">Penanggung Jawab</label>
                    <input type="text" class="form-control" name="pic" id="penanggungJawab" placeholder="Susi Susanti"  value="{{ old('pic') ?? @$bast->pic  }}" required>
                </div>
                <hr>
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>

        </div>
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
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Quote'
        });
    });
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

