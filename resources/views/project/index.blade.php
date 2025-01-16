@extends('adminlte::page')

@section('content_header')
    <h1>Data Proyek</h1>
@stop
@php
$no = ($project->currentPage() - 1) * $project->perPage() + 1;
$totalProjects = $totalProject + 1; // Get the total number of projects

@endphp
@section('content')

<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Berhasil Menambahkan Proyek</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Proyek Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Berhasil Menghapus Proyek</div>
    @endif
    @if(Session::get('project_close'))
    <div class="alert alert-danger mt-3">Proyek Sudah Ditutup</div>
    @endif
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
<div class="container">
    @canAccess('store','projects')
    <p id="projectNo"></p>
    @if(@$projectEdit)
    <form method="post" action="{{ route('project.update',@$projectEdit) }}">
    @method('put')
    @else
    <form method="post" action="{{ route('project.store') }}">
    @endif
        @csrf
        <div class="form-group">
            <label>Nama Proyek</label>
            <input type="text" class="form-control" name="title" placeholder="Nama Proyek" value="{{ old('title') ?? @$projectEdit->title }}" required>
        </div>

        <div class="form-group">
            <label>Surat Perintah Kerja</label>
            <select name="work_order" class="form-control select2"  id="">
                <option value="" selected disabled>Pilih Surat Perintah Kerja</option>
                @foreach($workOrder as $a)
                <option value="{{ $a->id }}" {{ @$projectEdit->work_order_id == $a->id ? 'selected' : '' }}> {{ $a->number_result }} </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Jangka Waktu Pekerjaan</label>
            <div class="input-group">
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') ?? @$projectEdit->start_date }}" required>
                <div class="input-group-append">
                    <span class="input-group-text">hingga</span>
                </div>
                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') ?? @$projectEdit->end_date }}" required>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan Proyek</label>
            <textarea class="form-control" rows="3" name="description" placeholder="Type here">{{ old('description') ?? @$projectEdit->description }}</textarea>
        </div>

        <div class="form-group">
            <input type="checkbox" id="recurringCheckbox" name="recurring" {{ @$projectEdit->recurring ? 'checked' : '' }}>
            <label for="recurringCheckbox">Recurring Proyek</label>
        </div>

        <div class="form-group">
            <input type="checkbox" id="alertCheckbox" name="alertCheckbox" {{ @$projectEdit->alert_expired ? 'checked' : '' }}>
            <label for="alertCheckbox">Aktifkan Peringatan</label>
        </div>
        
        <div id="alertOptions" style="{{ @$projectEdit->alert_expired ? 'display: block;' : 'display: none;' }}">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="expired" id="expired" disabled {{ @$projectEdit->alert_expired ? 'checked' : '' }}>
                <label class="form-check-label" for="expired">Expired</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="one_week" id="oneWeek" {{ @$projectEdit->alert_one_week ? 'checked' : '' }}>
                <label class="form-check-label" for="oneWeek">1 Minggu</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="two_week" id="twoWeeks" {{ @$projectEdit->alert_two_week ? 'checked' : '' }}>
                <label class="form-check-label" for="twoWeeks">2 Minggu</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="one_month" id="oneMonth" {{ @$projectEdit->alert_one_month ? 'checked' : '' }}>
                <label class="form-check-label" for="oneMonth">1 Bulan</label>
            </div>
        </div>


        @if(@$projectEdit)
        <button type="submit" class="btn btn-primary">Ubah</button>
        @if($directManager)
        <a href="{{ route('manager.edit',$directManager)}}" class="btn btn-info">Jumlah Hari Kerja</a>
        @endif
        @else
        <button type="submit" class="btn btn-primary">Simpan</button>
        @endif
    </form>

    <hr>
    @endcanAccess
    
    <form action="{{ route('project.index') }}" method="get">
        <div class="d-flex flex-row-reverse">
            <div class="p-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                @canAccess('export','projects')
                <a href="{{ route('project.export') }}" class="btn btn-success"><i class="fa fa-file-excel"></i></a>
                @endcanAccess
            </div>
            <div class="p-2">
                <input type="text" name="search" class="form-control" placeholder="Search">
            </div>
            <div class="p-2">
            @php
                $order = request('order', 'desc');
            @endphp
                <select name="order" class="form-control">
                    <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Created By</option>
                    <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Created By</option>
                </select>
            </div>
            <div class="p-2">
                <select name="status" class="form-control">
                    <option value="" disabled selected>-- Status --</option>
                    <option value="open"  >Open</option>
                    <option value="close" >Close</option>
                </select>
            </div>
        </div>
    </form>

        
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No Proyek</th>
                <th>Nama Proyek</th>
                <th>Status</th>
                <th>Timeline</th>
                <th>Progress</th>
                <th>Nomor SPK</th>
                <th>Total SPK</th>
                <th>Pic</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($project as $a)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $a->title }}</td>
                <td>
                    {!! $a->status_project == 'open' 
                        ? '<span class="badge badge-success">Open</span>' 
                        : '<span class="badge badge-danger">Close</span>' !!}
                </td>
                <td> {{ $a->progress_percentage. "%"  ?? "0%" }} </td>
                <td>
                    {{ $a->progress_task."%" ?? "0%"  }}
                </td>
                <td>{{ $a->workOrder ? $a->workOrder->number_result : '' }}</td>
                <td>{{ $a->workOrder ? 'Rp. '.number_format($a->workOrder->total,0,',','.') : '' }}</td>
                <td>
                    {{ $a->user ? $a->user->name : '' }}
                </td>
                <td>
                    <form method="post" action="{{ route('project.destroy',$a) }}">
                        @csrf
                        @method('delete')
                        @canAccess('show','projects')
                        <a href="{{ route('project.show',$a->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                        @endcanAccess
                        @if($a->status_project == 'open')
                        @canAccess('edit','projects')
                        <a href="{{ route('project.edit',$a->slug) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                        @canAccess('destroy','projects')
                        <button onclick="return window.confirm('{{ __('Apakah Anda Yakin Hapus Data ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                        @endcanAccess
                        @endif
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <center>Data Kosong</center>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-2">
        {{ $project->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>
</div>


@stop

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    // Checkbox alert
    const alertCheckbox = document.getElementById('alertCheckbox');
    const expiredCheckbox = document.getElementById('expired');
    const alertOptions = document.getElementById('alertOptions');
    
    const oneWeekCheckbox = document.getElementById('oneWeek');
    const twoWeeksCheckbox = document.getElementById('twoWeeks');
    const oneMonthCheckbox = document.getElementById('oneMonth');

    alertCheckbox.addEventListener('change', function() {
        alertOptions.style.display = this.checked ? 'block' : 'none';
        expiredCheckbox.checked = this.checked; // Check Expired automatically when Alert is checked
        expiredCheckbox.disabled = this.checked; // Disable Expired checkbox when Alert is checked
        if(!this.checked)
        {
            expiredCheckbox.checked = false;
            oneWeekCheckbox.checked = false;
            twoWeeksCheckbox.checked = false;
            oneMonthCheckbox.checked = false;
        }
    });
</script>
<script>
    $(document).ready(function () 
    {
        $('.select2').select2({
            width: '100%',
        });
    });

    $(document).ready(function () 
    {
        let nomor = "{{ $totalProjects }}";
        document.getElementById('projectNo').innerHTML = "No Proyek :"+nomor;


        let getPrice = document.getElementById("budget").value;
        if (getPrice) 
        {
            document.getElementById("budget_show").value = getPrice;
            formatRupiahFormat(document.getElementById("budget_show"),"budget"); // Format default value
        }

    });
    function formatRupiahFormat(input, inputNonFormat) 
    {
        let numStr = input.value.toString().replace(/[^,\d]/g, '');
        let split = numStr.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

        if (numStr === "" || parseInt(numStr) === 0) {
            input.value = '0';
            numStr = 0;
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }
</script>
@stop
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    body {
        font-family: Arial, sans-serif;
        /* padding: 20px; */
        background-color: #f4f4f4;
    }
    .container {
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
    }
    table 
    {
        width: 100%;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    th {
        background-color: #f2f2f2;
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


