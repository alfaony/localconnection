@extends('adminlte::page')

@section('content_header')
    <h1 id="quote_title">Detail Invoice {{ $invoice->number_result }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(Session::get('successEmail'))
        <div class="alert alert-success mt-3">Email Berhasil Dikirim</div>
        @endif
        @if(Session::get('successEmail'))
        <div class="alert alert-success mt-3">Email Berhasil Dikirim</div>
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
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Berhasil Menambahkan Invoice</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Invoice Berhasil Diperbarui</div>
        @endif
        @if(Session::get('delete'))
        <div class="alert alert-success mt-3">Berhasil Menghapus Invoice</div>
        @endif
        @if(Session::get('xero'))
        <div class="alert alert-success mt-3">Berhasil Terhubung Xero</div>
        @endif
        @if(Session::get('AUTHORISED'))
        <div class="alert alert-success mt-3">Invoice Dalam Proses Pembayaran</div>
    @endif
    </div>
</div>
<div class="row">
    <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Invoice Summary</h3>
                    <div>
                        <a href="{{ route('invoice.index') }}" class="btn btn-info btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Invoice
                        </a>
                        @if(($invoice->status != 'PAID') && ($invoice->status != 'DELETED') && ($invoice->status != 'VOID') && ($invoice->status != 'AUTHORISED'))
                        <a href="{{ route('invoice.edit', $invoice->slug) }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i> Edit Invoice
                        </a>
                        @endif
                        <a href="{{ route('invoice.download.pdf', ['slug' => $invoice->slug]) }}" class="btn btn-success btn-sm">
                            <i class="fa fa-file-pdf"></i> Download Invoice
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Invoice Number:</strong> {{ $invoice->number_result }}</p>
                            <p><strong>Reference:</strong> {{ $invoice->reference ?? '-' }}</p>
                            <p><strong>Customer Name:</strong> {{ $invoice->quote->customer->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Start Date:</strong> {{ $invoice->start_date->format('d M Y') }}</p>
                            <p><strong>Due Date:</strong> {{ $invoice->end_date->format('d M Y') }}</p>
                            <p><strong>Status:</strong>
                             @switch($invoice->status)
                                    @case('DRAFT')
                                        <span class="badge badge-secondary">DRAFT</span>
                                        @break

                                    @case('SUBMITTED')
                                        <span class="badge badge-warning">SUBMITTED</span>
                                        @break

                                    @case('AUTHORISED')
                                        <span class="badge badge-success">WAITING PAYMENT</span>
                                        @break

                                    @case('PAID')
                                        <span class="badge badge-success">PAID</span>
                                        @break

                                    @default
                                        <span class="badge badge-danger">{{ $invoice->status }}</span>
                                @endswitch
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p><strong>Grand Total:</strong> {{ 'Rp ' . number_format($invoice->total, 0, ',', '.') }}</p>
                            <p><strong>Tax:</strong> {{ $invoice->tax }}%</p>
                            <p><strong>Service Fee:</strong> {{ $invoice->service_fee }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-7">
        <!-- Accordion for Invoice, BAST, and Project Report -->
        <div id="accordion">
            <!-- Invoice Details Section -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link text-dark" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Invoice Details
                        </button>
                    </h5>
                    @canAccess('downloadPdfA','invoices')
                    @canAccess('checkPdfAStatus','invoices')
                    @canAccess('clearsessionPdfA','invoices')
                    <div class="ml-auto">
                        <a href="{{ route('invoice.download.pdfa', ['slug' => $invoice->slug]) }}" class="btn btn-success btn-sm">
                            <i class="fa fa-file-pdf"></i> Download PDF/A
                        </a>
                    </div>
                    @endcanAccess
                    @endcanAccess
                    @endcanAccess
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        <iframe src="{{ s3_asset(true,10,$invoice->file_merge_path) }}" width="100%" height="500px" frameborder="0"></iframe>
                    </div>
                </div>
            </div>

            <!-- BAST Section -->
            @if ($bast)
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link text-dark" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            BAST Details
                        </button>
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                    <div class="card-body">
                        <h5>Berita Acara Serah Terima</h5>
                        <p>No. {{ $bast->number_result ?? '-' }}</p>
                        <table class="table table-bordered">
                            <tr>
                                <th>Nomor</th>
                                <td>{{ $bast->number_result }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td>{{ $today ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>No. Purchase Order</th>
                                <td>{{ $bast->number_purchase ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Penanggung Jawab</th>
                                <td>{{ $bast->pic ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Perusahaan</th>
                                <td>{{ $bast->workOrder->quote->customer->name ?? '-' }}</td>
                            </tr>
                        </table>
                         <a href="{{ route('bast.download.pdf',$bast->slug) }}" class="btn btn-success mt-3"> <i class="fa fa-file-pdf"></i> Download BAST</a>
                         <a href="{{ route('bast.show',$bast->slug) }}" class="btn btn-primary mt-3"> <i class="fa fa-eye"></i> Lihat BAST</a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Project Report Section -->
            @if ($bast->project && $bast->project->reportProject)
            <div class="card">
                <div class="card-header" id="headingThree">
                    <h5 class="mb-0">
                        <button class="btn btn-link text-dark" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Project Report
                        </button>
                    </h5>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                    <div class="card-body">
                        <h5>Laporan Proyek</h5>
                        <p>No Report: {{ $bast->project->reportProject->number_result }}</p>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Link</th>
                                        <th>File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bast->project->reportProject->reportProjectDetail as $index => $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $detail->name }}</td>
                                        <td>{{ $detail->link }}</td>
                                        <td>
                                            <a href="{{ s3_asset(true,10,'reports/' . $detail->file) }}" class="btn btn-sm btn-primary" download><i class="fa fa-download"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('report-project.show',$bast->project->reportProject->slug) }}" class="btn btn-success mt-3"><i class="fa fa-eye"></i> Show</a>
                        <a href="{{ route('report-project.downloadall', ['slug' => $bast->project->reportProject->slug]) }}" class="btn btn-success mt-3"><i class="fa fa-download"></i> Download All</a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @canAccess('sentMail','invoices')
    <div class="col-md-5">
        <!-- Email Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kirim Email</h3>
            </div>
            <div class="card-body">
                @if ($bast->file_merge_path)
                <form action="{{ route('invoice.sentMail', $invoice->slug) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="to">To:</label>
                        <select name="to[]" class="form-control select2" multiple="multiple" required>
                            @if ($bast->workOrder)
                                <option value="{{ $bast->workOrder->quote->customer->email }}" selected>{{ $bast->workOrder->quote->customer->email }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cc">CC:</label>
                        <select name="cc[]" class="form-control select2" multiple="multiple">
                            <option value="{{ Auth::user()->email }}" selected>{{ Auth::user()->email }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" name="subject" class="form-control" placeholder="Masukkan subject email">
                    </div>
                    <div class="form-group">
                        <label for="content">Isi:</label>
                        <input class="thriveEditor form-control" id="description_content_invoice" data-ids="content_invoice" name="content" rows="3" placeholder="yang akan dicetak di perjanjian"/>
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="this.form.submit(); this.disabled = true;">Kirim Email</button>
                </form>
                @else
                <p class="text-muted">No PDF available.</p>
                @endif
            </div>
        </div>

        <!-- Email History -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Riwayat Email Invoice</h3>
            </div>
            <div class="card-body">
                @if ($invoiceEmailRecords->count() > 0)
                    @foreach ($invoiceEmailRecords as $record)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="card-title">Subject : {{ $record->subject }}</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><strong>To:</strong> {{ implode(', ', json_decode($record->to, true)) }}</p>
                                <p class="mb-0"><strong>CC:</strong> {{ implode(', ', json_decode($record->cc, true) ?? []) }}</p>
                                <p class="mb-0"><strong>Tanggal Dikirim:</strong> {{ \Carbon\Carbon::parse($record->created_at)->format('d M Y H:i') }}</p>
                                <p class="mb-0"><strong>Dibuat:</strong> {{ $record->user ? $record->user->name : '-' }}</p>
                            </div>
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $invoiceEmailRecords->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                    </div>
                @else
                    <p class="text-center">Belum ada riwayat email yang dikirim.</p>
                @endif
            </div>
        </div>
    </div>
    @endcanAccess
</div>
@stop

@section('js')
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
@canAccess('downloadPdfA','invoices')
@canAccess('checkPdfAStatus','invoices')
@canAccess('clearsessionPdfA','invoices')
@if(Session::get('downloadPdfA'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let isDownloaded = false; // Track if file has been downloaded
        const loadingOverlay = document.createElement('div');
        
        // Add a loading overlay
        loadingOverlay.innerHTML = `
            <div id="loading-overlay" style="display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; color: white; font-size: 20px;">
                <div>
                    <div class="spinner-border text-light" role="status"></div>
                    <p>Exporting your file, please wait...</p>
                </div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);

        const checkExportStatus = () => {
            if (isDownloaded) return; // Stop if already downloaded

            fetch('{{ route('invoice.checkPdfAStatus') }}')
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    
                    if (data.ready) {
                        isDownloaded = true; // Mark as downloaded

                        // Create a hidden download link to trigger download
                        const downloadLink = document.createElement('a');
                        downloadLink.href = data.download_url;
                        downloadLink.style.display = 'none';
                        downloadLink.download = ''; // Optional: specify a filename
                        
                        document.body.appendChild(downloadLink);
                        
                        // Add onload callback to clear session after download
                        downloadLink.onclick = () => {
                            // Clear export session AFTER file download starts
                            fetch('{{ route('invoice.clearsessionPdfA') }}')
                                .then(() => {
                                    // Hide the loading overlay
                                    document.getElementById('loading-overlay').remove();
                                })
                                .catch(error => console.error('Error clearing session:', error));
                        };

                        // Trigger download
                        downloadLink.click();

                        // Remove the link element after triggering download
                        document.body.removeChild(downloadLink);
                    } else {
                        setTimeout(checkExportStatus, 3000); // Retry every 3 seconds
                    }
                })
                .catch(error => {
                    console.error('Error checking export status:', error);
                    // Hide loading overlay if error occurs
                    document.getElementById('loading-overlay').remove();
                });
        };

        checkExportStatus();
    });
</script>
@endif
@endcanAccess
@endcanAccess
@endcanAccess

<script>
    $(document).ready(function() {
        $('.select2').select2({
            tags: true,
            placeholder: 'Pilih email',
            allowClear: true,
            width: '100%'
        });

        $('.selectNonMultiple2').select2({
            placeholder: 'Pilih Versi',
            allowClear: true,
            width: '100%'
        });
    });
    
</script>
@stop

@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 6px;
        height: auto;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
        padding: 3px 10px;
    }
    .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
    }
    .select2-selection__arrow {
        height: 36px !important;
    }
    .ql-container 
    {
        min-height: 150px;
        height: auto;
    }
</style>
@endsection

