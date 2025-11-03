@extends('adminlte::page')

@section('title', 'Show Project')

@section('content')

<div class="card p-3 mb-2 mt-3">
    <div class="card-body">
        <h2>Detail Proyek</h2>
        <div class="col-md-12">
            @if(Session::get('deletePurchase'))
                <div class="alert alert-success mt-3">Berhasil Menghapus Pembelian</div>
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

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Nama Proyek</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->title ?? '' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Surat Perintah Kerja</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->workOrder->number_result ?? '' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Jangka Waktu Pekerjaan</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($projectEdit->start_date)->format('d-m-Y') ?? '' }} hingga {{ \Carbon\Carbon::parse($projectEdit->end_date)->format('d-m-Y')?? '' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Keterangan Proyek</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->description ?? '' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Recurring Proyek</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->recurring ? 'Yes' : 'No' }}</p>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Aktifkan Peringatan</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $projectEdit->alert_expired ? 'Yes' : 'No' }}</p>
            </div>
        </div>

        @if($projectEdit->alert_expired)
        <div class="form-group row mt-3">
            <label class="col-sm-2 col-form-label">Peringatan</label>
            <div class="col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="expired" id="expired" disabled {{ $projectEdit->alert_expired ? 'checked' : '' }}>
                    <label class="form-check-label" for="expired">Expired</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="one_week" id="oneWeek" disabled {{ $projectEdit->alert_one_week ? 'checked' : '' }}>
                    <label class="form-check-label" for="oneWeek">1 Minggu</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="two_week" id="twoWeeks" disabled {{ $projectEdit->alert_two_week ? 'checked' : '' }}>
                    <label class="form-check-label" for="twoWeeks">2 Minggu</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="one_month" id="oneMonth" disabled {{ $projectEdit->alert_one_month ? 'checked' : '' }}>
                    <label class="form-check-label" for="oneMonth">1 Bulan</label>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2>Surat Perintah Kerja</h2>
        <div class="col-md-12">
            @if(Session::get('deletePurchase'))
                <div class="alert alert-success mt-3">Berhasil Menghapus Pembelian</div>
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

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="mt-5">No SPK: {{ $workOrder->number_result }}</div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label for="date" class="col-sm-4 col-form-label text-right">Tanggal:</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext">{{ $workOrder->date }}</p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-right">Finance:</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext">{{ $workOrder->userCreate->name ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group row mb-3 mt-3">
            <label class="col-sm-2 col-form-label">Pilih No. Quote</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $workOrder->quote->number_result }}</p>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label class="col-sm-2 col-form-label">Customer</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $workOrder->quote->customer->name }}</p>
            </div>
        </div>

        <table class="table table-bordered" id="tableWorkOrder">
            <thead>
                <tr>
                    <th class="col-1">No</th>
                    <th class="col-3">Produk / Jasa</th>
                    <th class="col-3">Description</th>
                    <th class="col-2">Qty</th>
                    <th class="col-2">Budget</th>
                </tr>
            </thead>
            <tbody>
                @php $nomorBaris = 1; @endphp
                @foreach($workOrder->workOrderProduct->sortBy('sort') as $a)
                <tr>
                    <td class="col-1">{{ $nomorBaris++ }}</td>
                    <td class="col-3">{{ $a->product->name ?? "" }}</td>
                    <td class="col-3">{!! $a->description !!}</td>
                    <td class="col-2">{{ $a->qty }}</td>
                    <td class="col-2">{{ 'Rp. '.number_format($a->sub_total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mt-3">
            <div class="col-2 offset-10">
                <div class="d-flex justify-content-between mb-2">
                    <div>Total:</div>
                    <div id="sub_total_result">{{ 'Rp '.number_format($workOrder->total, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="fileUpload">Mohon Upload Quote yang sudah di tanda-tangani:</label>
            @if($workOrder->quote_file)
                <div class="mb-2">
                    <a href="{{ s3_asset(true,10,$workOrder->quote_file) }}" class="btn btn-sm btn-primary" download><i class="fa fa-file-pdf"></i> Download</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .form-control-plaintext {
        background-color: #f9f9f9;
    }
    .table th, .table td {
        vertical-align: middle;
    }
</style>
@endsection
