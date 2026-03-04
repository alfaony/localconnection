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
<div class="col-md-12">
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
            <div class="d-flex gap-2">
                <select name="work_order" class="form-control select2" id="spkSelect" style="flex: 1;">
                    <option value="" selected disabled>Pilih Surat Perintah Kerja</option>
                    @foreach($workOrder as $a)
                    <option value="{{ $a->id }}" {{ @$projectEdit->work_order_id == $a->id ? 'selected' : '' }}>{{ $a->number_result }} </option>
                    @endforeach
                </select>
                @canAccess('getSpkDetails','projects')
                <button type="button" id="viewSpkDetailsBtn" class="btn btn-info" style="display: none; white-space: nowrap;">
                    <i class="fas fa-eye"></i> Detail SPK
                </button>
                @endcanAccess
            </div>
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
                <a href="javascript:void(0)" onclick="exportProject()" class="btn btn-success"><i class="fa fa-file-excel"></i></a>
                @endcanAccess
            </div>
            <div class="p-2">
                <input type="text" name="search" class="form-control" placeholder="Search" value="{{ request('search') }}">
            </div>
            <div class="p-2">
                <select name="division" class="form-control select2">
                    <option value="">-- Semua Divisi --</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->id }}" {{ request('division') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                    @endforeach
                </select>
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
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="close" {{ request('status') == 'close' ? 'selected' : '' }}>Close</option>
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
                    <div class="d-flex">
                        <form method="post" action="{{ route('project.destroy',$a) }}">
                            @csrf
                            @method('delete')
                            @canAccess('show','projects')
                            <a href="{{ route('project.show',$a->slug) }}" class="btn btn-info btn-sm mb-2"><i class="fa fa-eye"></i></a>
                            @endcanAccess
                            @if($a->status_project == 'open')
                            @canAccess('edit','projects')
                            <a href="{{ route('project.edit',$a->slug) }}" class="btn btn-primary btn-sm mb-2"><i class="fa fa-edit"></i></a>
                            @endcanAccess
                            @canAccess('destroy','projects')
                            <button onclick="return window.confirm('{{ __('Apakah Anda Yakin Hapus Data ? ') }}')" class="btn btn-danger btn-sm mb-2"><i class="fa fa-trash"></i></button>
                            @endcanAccess
                            @endif
                        </form>
                    </div>
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

@canAccess('getSpkDetails','projects')
<!-- SPK Details Modal -->
<div class="modal fade" id="spkDetailsModal" tabindex="-1" role="dialog" aria-labelledby="spkDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="spkDetailsModalLabel">
                    <i class="fas fa-file-contract"></i> Detail Surat Perintah Kerja
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="spkDetailsContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                        <p class="mt-2">Memuat data...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endcanAccess

@stop

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@canAccess('getSpkDetails','projects')
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

    // SPK Details Functionality
    const spkSelect = $('#spkSelect');
    const viewSpkBtn = $('#viewSpkDetailsBtn');
    const spkModal = $('#spkDetailsModal');
    const spkContent = $('#spkDetailsContent');

    // Show/hide button based on SPK selection
    spkSelect.on('change', function() {
        if ($(this).val()) {
            viewSpkBtn.fadeIn();
        } else {
            viewSpkBtn.fadeOut();
        }
    });

    // Check if SPK is already selected on page load
    if (spkSelect.val()) {
        viewSpkBtn.show();
    }

    // View SPK Details
    viewSpkBtn.on('click', function() {
        const spkId = spkSelect.val();
        if (!spkId) return;

        // Show modal with loading state
        new bootstrap.Modal(document.getElementById('spkDetailsModal')).show();
        spkContent.html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                <p class="mt-2">Memuat data...</p>
            </div>
        `);

        // Fetch SPK details via AJAX
        let url = "{{ route('project.getSpkDetails', ':id') }}";
        url = url.replace(':id', spkId);
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                let productsHtml = '';
                if (data.products && data.products.length > 0) {
                    productsHtml = `
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th>Nama Produk</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    
                    data.products.forEach((product, index) => {
                        productsHtml += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${product.name}</td>
                            </tr>`;
                    });
                    
                    productsHtml += `
                                </tbody>
                            </table>
                        </div>`;
                } else {
                    productsHtml = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Tidak ada produk</div>';
                }

                const detailsHtml = `
                    <!-- Customer Information Section -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-user"></i> Informasi Customer
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item mb-2">
                                        <label class="text-muted mb-1 small">
                                            <i class="fas fa-user-circle"></i> Nama Customer
                                        </label>
                                        <div class="font-weight-bold">${data.customer_name || '-'}</div>
                                    </div>
                                    <div class="info-item mb-2">
                                        <label class="text-muted mb-1 small">
                                            <i class="fas fa-envelope"></i> Email
                                        </label>
                                        <div>${data.customer_email || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-2">
                                        <label class="text-muted mb-1 small">
                                            <i class="fas fa-phone"></i> Telepon
                                        </label>
                                        <div>${data.customer_phone || '-'}</div>
                                    </div>
                                    <div class="info-item mb-2">
                                        <label class="text-muted mb-1 small">
                                            <i class="fas fa-map-marker-alt"></i> Alamat
                                        </label>
                                        <div>${data.customer_address || '-'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-file-invoice"></i> Informasi Quote & SPK
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted mb-1">
                                            <i class="fas fa-file-alt"></i> Nomor SPK
                                        </label>
                                        <div class="font-weight-bold h6">${data.spk_number || '-'}</div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted mb-1">
                                            <i class="fas fa-calendar"></i> Tanggal SPK
                                        </label>
                                        <div class="font-weight-bold">${data.spk_date || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted mb-1">
                                            <i class="fas fa-file-invoice"></i> Nomor Quotation
                                        </label>
                                        <div class="font-weight-bold h6">${data.quotation_number || '-'}</div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted mb-1">
                                            <i class="fas fa-user-tie"></i> Pembuat Quotation
                                        </label>
                                        <div class="font-weight-bold">${data.quote_name || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-item mb-3">
                                        <label class="text-muted mb-1">
                                            <i class="fas fa-user-tie"></i> Pembuat SPK
                                        </label>
                                        <div class="font-weight-bold">${data.creator_name || '-'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quote Transition Status -->
                    <div class="mt-3">
                        <div class="info-item">
                            <label class="text-muted mb-2">
                                <i class="fas fa-exchange-alt"></i> Status Quote Peralihan
                            </label>
                            <div>
                                ${data.is_transition ? 
                                    '<span class="badge badge-warning p-2" style="font-size: 1rem;"><i class="fas fa-exclamation-circle"></i> Quote Peralihan</span>' : 
                                    '<span class="badge badge-success p-2" style="font-size: 1rem;"><i class="fas fa-check-circle"></i> Quote Regular</span>'
                                }
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mt-4 mb-3">
                        <i class="fas fa-boxes"></i> Daftar Produk
                    </h6>
                    ${productsHtml}
                `;

                spkContent.html(detailsHtml);
            },
            error: function(xhr) {
                let errorMsg = 'Gagal memuat data SPK';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                spkContent.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${errorMsg}
                    </div>
                `);
            }
        });
    });
</script>
@endcanAccess
<script>
    $(document).ready(function () 
    {
        $('.select2').select2({
            width: '100%',
        });
    });

    function exportProject() {
        let division = $('select[name="division"]').val();
        let url = "{{ route('project.export') }}";
        if (division) {
            url += "?division=" + division;
        }
        window.location.href = url;
    }

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
    
    /* SPK Details Modal Styling */
    #spkDetailsModal .info-item label {
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    
    #spkDetailsModal .info-item div {
        font-size: 1rem;
    }
    
    #spkDetailsModal .card {
        border: 1px solid #e3e3e3;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    #spkDetailsModal .table thead th {
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    #spkDetailsModal .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .d-flex.gap-2 {
        gap: 0.5rem;
    }
    
    #viewSpkDetailsBtn {
        min-width: 150px;
    }

</style>
@stop


