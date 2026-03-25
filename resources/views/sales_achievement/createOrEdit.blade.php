@extends('adminlte::page')

@section('content')
@include('components.alert')
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

<div class="card p-3 mt-3 shadow-sm">
    <div class="card-header">{{ isset($salesAchievement) ? 'Edit Pencapaian Penjualan' : 'Tambah Pencapaian Penjualan' }}</div>
    <div class="card-body">
        <form action="{{ isset($salesAchievement) ? route('sales_achievement.update', $salesAchievement->slug) : route('sales_achievement.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($salesAchievement))
                @method('PUT')
            @endif
            <div class="mb-3">
                <label for="period" class="form-label">Periode Capaian Penjualan</label>
                <select class="form-control @error('period') is-invalid @enderror" id="period" name="period" >
                    @foreach ($months as $key => $month)
                        <option value="{{ $key }}" {{ old('period', isset($salesAchievement) ? $salesAchievement->period : '') == $key ? 'selected' : '' }}>
                            {{ $month }}
                        </option>
                    @endforeach
                </select>
                @error('period')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="sales_amount" class="form-label">Capaian Penjualan</label>
                <input type="text" class="form-control @error('sales_amount') is-invalid @enderror"  id="sales_amount_show"  oninput="formatRupiahFormat(this,'sales_amount')" required/>
                <input type="hidden" name="sales_amount" id="sales_amount" value="{{ old('sales_amount', isset($salesAchievement) ? $salesAchievement->sales_amount : '') }}" >
                @error('sales_amount')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="total_presentations" class="form-label">Jumlah Presentasi</label>
                <input type="number" class="form-control @error('total_presentations') is-invalid @enderror" id="total_presentations" name="total_presentations" value="{{ old('total_presentations', isset($salesAchievement) ? $salesAchievement->total_presentations : '') }}" >
                @error('total_presentations')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="total_offers_issued" class="form-label">Jumlah Penawaran Diterbitkan</label>
                <input type="number" class="form-control @error('total_offers_issued') is-invalid @enderror" id="total_offers_issued" name="total_offers_issued" value="{{ old('total_offers_issued', isset($salesAchievement) ? $salesAchievement->total_offers_issued : '') }}" >
                @error('total_offers_issued')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="customer_visits" class="form-label">Jumlah Kunjungan Pelanggan</label>
                <input type="number" class="form-control @error('customer_visits') is-invalid @enderror" id="customer_visits" name="customer_visits" value="{{ old('customer_visits', isset($salesAchievement) ? $salesAchievement->customer_visits : '') }}" >
                @error('customer_visits')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="registered_customers" class="form-label">Jumlah Pelanggan Daftar</label>
                <input type="number" class="form-control @error('registered_customers') is-invalid @enderror" id="registered_customers" name="registered_customers" value="{{ old('registered_customers', isset($salesAchievement) ? $salesAchievement->registered_customers : '') }}" >
                @error('registered_customers')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="active_customers" class="form-label">Jumlah Pelanggan Aktif</label>
                <input type="number" class="form-control @error('active_customers') is-invalid @enderror" id="active_customers" name="active_customers" value="{{ old('active_customers', isset($salesAchievement) ? $salesAchievement->active_customers : '') }}" >
                @error('active_customers')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            @if(@$salesAchievement->points && @$salesAchievement->user->approvement_user_id == Auth::user()->id)
            <div class="mb-3">
                <label for="points" class="form-label">Poin</label>
                <input type="number" class="form-control" id="points" name="point" value="{{ old('point', isset($salesAchievement) ? $salesAchievement->points : '') }}" >
                @error('point')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @endif

            @if(@$salesAchievement->points && @$salesAchievement->user->approvement_user_id == Auth::user()->id)
                <button type="submit" class="btn btn-primary">{{ isset($salesAchievement) ? 'Ubah' : 'Simpan' }}</button>
            @else
                @if(Auth::user()->approvement_user_id)
                <button type="submit" class="btn btn-primary">{{ isset($salesAchievement) ? 'Ubah' : 'Simpan' }}</button>
                @else
                <div class="mt-5">
                        <span class="alert alert-warning" role="alert">
                            Silahkan hubungi admin atau atasan Anda untuk memberikan approval.
                        </span>
                    </div>
                @endif
            @endif
        </form>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        let discount = document.getElementById("sales_amount").value;
        if (discount)
        {
            document.getElementById("sales_amount_show").value = discount;
            formatRupiahFormat(document.getElementById("sales_amount_show"),"sales_amount"); // Format default value
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
            input.value = 'Rp. '+rupiah;
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
        border-radius: 5px;
    }
</style>
@stop
