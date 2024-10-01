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
                    {{-- 
                    <label for="pilihSPK" class="form-label">Pilih SPK</label>
                    <select class="form-control select2" name="work_order" id="pilihSPK" required>
                        <option value="" selected disabled>Pilih</option>
                        @foreach($workOrder as $a)
                        <option value="{{ $a->id }}" {{ @$bast->work_order_id == $a->id ? 'selected' : '' }}>{{ $a->number_result }}</option>
                        @endforeach
                        <!-- Other options can be added here -->
                    </select>
                    <select class="form-control" id="work_order" name="work_order" required></select>
                    --}}
                </div>
                <div class="form-group">
                    <label for="pilihDataProyek" class="form-label">Pilih Data Proyek</label>
                    <select class="form-control select2 projectChange" name="project" id="" required>
                        <option value="" disabled selected>Pilih</option>
                        @foreach($project as $a)
                        <option value="{{ $a->id }}" data-report="{{ $a->reportProject ? $a->reportProject->id : '' }}" {{ @$bast->project_id == $a->id ? 'selected' : '' }} {{ @$selectedWorkOrder->id == $a->work_order_id ? 'selected' : '' }}>{{ $a->title }} -  {{ $a->workOrder->number_result }}</option>
                        @endforeach     
                        <!-- Other options can be added here -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="pilihDataProyek" class="form-label">Status Laporan Proyek</label>
                    <span class="form-control" id="reportProjectMessage"></span>
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
                <div class="form-group">
                    <label for="signature">Bertanda Tangan</label>
                    <select class="form-control select2" name="customer_signature" id="signature" required>
                        <option value="" disabled selected>Pilih</option>
                        @foreach($signature as $a => $value)
                        <option value="{{ $a }}" {{ @$bast->customer_signature == $a ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                        <!-- Other options can be added here -->
                    </select>
                </div>
                <hr>
                <div class="form-group mt-4">
                    <button type="submit" id="saveButtonId" class="btn btn-primary">Simpan</button>
                </div>
            </form>

        </div>
    </div>
</div>
@stop
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function () 
    {
        suggestSelect();

        $('.select2').select2({
            width: '100%',
            // placeholder: 'Pilih Quote'
        });

        $('#work_order').select2({
            placeholder: 'Pilih Surat Perintah Kerja',
            ajax: 
            {
                url: "{{ route('work-order.select2') }}",
                dataType: 'json',
                data: function(params) {
                    return {
                        number_result: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(workOrder) {
                            return {
                                id: workOrder.id,
                                text: workOrder.number_result,
                            };
                        })
                    };
                }
            }
        });

        var selectedValueWorkOrder = "{{ @$selectedWorkOrder->id }}";
        if(selectedValueWorkOrder)
        {
            title = "{{ @$selectedWorkOrder->number_result }}";
            // Create an option element with the selected value
            var newOption = new Option(title, selectedValueWorkOrder, true, true);
    
            // Append the option to the select2 element and trigger change
            $('#work_order').append(newOption).trigger('change');
        }

        var selectedValueQuote = "{{ @$bast->work_order_id }}";
        if(selectedValueQuote)
        {
            title = "{{ @$bast->workOrder->number_result }}";
            // Create an option element with the selected value
            var newOption = new Option(title, selectedValueQuote, true, true);
    
            // Append the option to the select2 element and trigger change
            $('#work_order').append(newOption).trigger('change');
        }

        $('.projectChange').on('change', function() {
        // Ambil data-report dari option yang dipilih
        var reportId = $(this).find(':selected').data('report');
        
        // Jika reportId kosong, tampilkan pesan dan disable tombol simpan
        if (!reportId) {
            $('#reportProjectMessage').text('Laporan Proyek Tidak Tersedia').addClass('text-red').removeClass('text-green');
            $('#saveButtonId').prop('disabled', true);  // diasumsikan bahwa tombol simpan memiliki id 'saveButtonId'
        } else {
            // Jika reportId ada, sembunyikan pesan dan aktifkan tombol simpan
            $('#reportProjectMessage').text('Laporan Proyek Tersedia').addClass('text-green').removeClass('text-red');;
            $('#saveButtonId').prop('disabled', false);
        }
    });

    $('.projectChange').trigger('change');
    // Jika Anda ingin memeriksa kondisi saat pertama kali halaman dimuat (misalnya jika select sudah memiliki option yang dipilih),
    // Anda bisa memicu event change pada select saat halaman selesai dimuat
    });

    function suggestSelect()
    {
        var selectWorkOrder ="{{ @$selectedWorkOrder->id ?? ''}}"
        var selectProject ="{{ @$selectedWorkOrder->project->id ?? ''}}"
        var selectProjectName ="{{ @$selectedWorkOrder->project->title ?? ''}}"

        if(selectWorkOrder && selectProject)
        {
            $('#pilihSPK').val(selectWorkOrder).trigger('change');
            $('#pilihDataProyek').val(selectProject).trigger('change');
            console.log(selectProjectName);
            
        }
        
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

