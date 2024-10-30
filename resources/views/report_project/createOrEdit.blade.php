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
        
                {{-- 
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Pilih SPK</label>
                        <select class="form-control select2" name="work_order" id="chooseSpk" required>
                            <option value="" disabled selected>Pilih SPK</option>
                            @foreach($workOrder as $a)
                            <option value="{{ $a->id }}" {{  @$reportProject->work_order_id == $a->id ? 'selected'  : ''}} >{{ $a->number_result }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" id="work_order_id" value="{{ @$reportProject->work_order_id }}">
                        <select class="form-control" id="work_order" name="work_order" required></select>
                    </div>
                </div>
                --}}
        
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Pilih Data Proyek</label>
                        <select class="form-control select2" name="project" id="" required>
                            @foreach($project as $a)
                            <option value="{{ $a->id }}" {{  @$reportProject->project_id == $a->id ? 'selected'  : ''}} {{ @$selectedProject->id == $a->id ? 'selected' : '' }}>{{ $a->title }} {{ $a->workOrder->number_result }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
        
                {{-- 
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
                --}}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-success" id="addRowFileUpload"><i class="fa fa-plus"></i> Laporan</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered mt-3" id="tableReport">
                        <thead>
                            <tr>
                                <th >No</th>
                                <th >Nama</th>
                                <th >Laporan</th>
                                <th >Link</th>
                                <th >File</th>
                                <th >Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(@$reportProject)
                            @php $no = 1; @endphp
                            @foreach(@$reportProject->reportProjectDetail as $index => $a)
                            <tr>
                                <td>
                                    {{ $no++ }}
                                </td>
                                <td width="30%">
                                    <input type="hidden" class="form-control" name="ids[]" value="{{ $a->id }}" required>
                                    <input type="text" class="form-control" id="name_" name="name[]" value="{{ $a->name }}" required>
                                </td>
                                <td>
                                    <!-- Hidden input default 0 -->
                                    <input type="hidden" name="is_report[{{ $index }}]" value="0">
                                    <!-- Checkbox is_report -->
                                    <input type="checkbox" class="form-control is_report_checkbox" name="is_report[{{ $index }}]" value="1" {{ $a->is_report ? 'checked' : '' }}>
                                </td>
                                <td width="30%">
                                    <input type="text" class="form-control" id="link_" name="link[]" value="{{ $a->link }}" required>
                                </td>
                                <td width="20%">
                                <input type="file" class="form-control" id="file_" name="file[]" accept=".pdf" >
                                </td>
                                <td>
                                    <a href="{{ Storage::url('reports/' . $a->file) }}" class="btn btn-sm btn-primary" download title="{{ $a->file }}"><i class="fa fa-download"></i></a>
                                    <button class="btn btn-sm btn-danger btnHapusData" data-id="{{ $a->id }}" title="delete"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function () {
        suggestSelect();

        $("#addRowFileUpload").click(function (e) 
        { 
            e.preventDefault();
            var noBaris = $('#tableReport tbody tr').length + 1; // Menghitung jumlah baris untuk nomor baris selanjutnya

            let row = `
                <tr>
                    <td>
                        ${noBaris}
                    </td>
                    <td>
                        <input type="hidden" class="form-control" name="ids[]" required>
                        <input type="text" class="form-control" id="name_" name="name[]" required>
                    </td>
                    <td>
                        <!-- Hidden input default 0 -->
                        <input type="hidden" name="is_report[${noBaris - 1}]" value="0">
                        <!-- Checkbox is_report -->
                        <input type="checkbox" class="form-control is_report_checkbox" name="is_report[${noBaris - 1}]" value="1">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="link_" name="link[]" required>
                    </td>
                    <td>
                        <input type="file" class="form-control" id="file_" name="file[]" accept=".pdf" required>
                    </td>
                    <td>
                        <button class="btn btn-danger btnHapus"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            `;

            $('#tableReport tbody').append(row);
        });

        $('#tableReport').on('click','.btnHapus', function() 
        {
            $(this).closest('tr').remove();
            updateNomorBaris();
        });

        $('.btnHapusData').click(function() {
            var dataId = $(this).data('id');
            // Tampilkan konfirmasi penghapusan
            var userConfirmation = confirm("Apakah anda yakin untuk menghapus data ini?");
            
            if(userConfirmation) 
            {
                let url = "{{ route('report-project.destroy.detail',':id') }}";
                url = url.replace(':id',dataId);
                // $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
                // updateNomorBaris(); // Perbarui nomor baris
                // Jika user mengonfirmasi, lakukan request AJAX untuk menghapus data
                $.ajax({
                    type: "POST", // atau "DELETE" sesuai dengan metode yang Anda gunakan
                    url: url, // Gantikan dengan endpoint Anda
                    data: 
                    {
                        id: dataId,
                        _token: "{{ csrf_token() }}", // Untuk Laravel, tambahkan CSRF token
                        _method: "DELETE" // Untuk Laravel, tambahkan CSRF token
                    },
                    success: function(response) 
                    {
                        // console.log(response);
                        Swal.fire(
                            {
                            title: 'Berhasil!',
                            text: 'Berhasil Menghapus Data',
                            icon: 'success',
                            timer: 1500, // 3 detik
                            timerProgressBar: true,
                            showConfirmButton: false,
                            showConfirmButton: false, // Menghilangkan tombol OK/Confirm
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) 
                    {
                        alert("Terjadi kesalahan saat menghapus data");
                    }
                });

                $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
                updateNomorBaris(); // Perbarui nomor baris
            }
        });
    });
</script>
<script>
    $(document).ready(function () 
    {
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

        var selectedValueQuote = "{{ @$reportProject->work_order_id }}";
        if(selectedValueQuote)
        {
            title = "{{ @$reportProject->workOrder->number_result }}";
            // Create an option element with the selected value
            var newOption = new Option(title, selectedValueQuote, true, true);
    
            // Append the option to the select2 element and trigger change
            $('#work_order').append(newOption).trigger('change');
        }
    });

    function updateNomorBaris() 
    {
        $('#tableReport tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    function suggestSelect()
    {        
        var selectWorkOrder ="{{ @$selectedWorkOrder->id ?? ''}}"
        var selectProject ="{{ @$selectedWorkOrder->project->id ?? ''}}"
        var selectProjectName ="{{ @$selectedWorkOrder->project->title ?? ''}}"

        if(selectWorkOrder && selectProject)
        {
            $('#chooseSpk').val(selectWorkOrder).trigger('change');
            $('#chooseProject').val(selectProject).trigger('change');
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

