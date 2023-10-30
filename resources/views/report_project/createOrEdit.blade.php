@extends('adminlte::page')
@section('content')
<div class="containe mt-3">
<div class="col-md-12">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
    <div class="card">
        <div class="card-body">
            @if(@$reportProject)
            <form method="post" action="{{ route('report-project.update',@$reportProject) }}" enctype="multipart/form-data">
            @method('patch')
            @else
            <form method="post" action="{{ route('report-project.store') }}" enctype="multipart/form-data">
            @endif
                @csrf
                <div class="form-group row">
                    <div class="col-md-6">
                        <h2>Laporan Proyek</h2>
                        <div class="mt-5">No Report: {{ $nomorReportProject ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                            <div class="col-sm-4">
                                <input type="date" name="date" class="form-control" id="date" value="{{ old('date') ?? @$reportProject->date }}" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <label class="col-sm-8 col-form-label text-right">PM:</label>
                            <div class="col-sm-4">
                                <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Pilih SPK</label>
                        <select class="form-control select2" name="work_order" required>
                            <option value="" disabled selected>Pilih SPK</option>
                            @foreach($workOrder as $a)
                            <option value="{{ $a->id }}" {{  @$reportProject->work_order_id == $a->id ? 'selected'  : ''}} >{{ $a->number_result }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
        
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Pilih Data Proyek</label>
                        <select class="form-control select2" name="project" required>
                        <option value="" disabled selected>Pilih Proyek</option>
                            @foreach($project as $a)
                            <option value="{{ $a->id }}" {{  @$reportProject->project_id == $a->id ? 'selected'  : ''}} >{{ $a->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
        
                <hr>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Masukan link laporan :</label>
                        <input type="text" class="form-control" placeholder="Placeholder" name="link_report" value="{{ old('date') ?? @$reportProject->link_report }}" required>
                    </div>
                </div>
        
                <div class="mb-3">
                    <label>Mohon upload file report :</label>
                    @if(@$reportProject)
                    <div class="mb-2">
                        <a href="{{ Storage::url('reports/' . $reportProject->report_file) }}" class="btn btn-sm btn-primary" download><i class="fa fa-file-pdf"></i> Download</a>
                    </div>
                    <input type="file" class="form-control" id="customFile" name="report_file" accept=".pdf">
                    
                    @else
                    <input type="file" class="form-control" id="customFile" name="report_file" accept=".pdf" required>
                    @endif
                </div>

                <hr>
                <div class="row mb-3">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
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
            // placeholder: 'Pilih Quote'
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

