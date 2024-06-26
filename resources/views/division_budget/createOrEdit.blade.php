@extends('adminlte::page')

@section('title', 'Anggaran Divisi')

@section('content_header')
    <h1>{{ @$divisionBudget ? 'Ubah Pengajuan Anggaran' : 'Pengajuan Anggaran' }}</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ @$divisionBudget ? route('division-budget.update', $divisionBudget->slug) : route('division-budget.store') }}" method="post">
            @csrf
            @if(@$divisionBudget)
            @method('PUT')
            @endif

            <div class="form-group">
                <label for="division_id">Divisi</label>
                <select name="division_id" id="division_id" class="form-control" required>
                    @foreach($divisions as $division)
                    <option value="{{ $division->id }}" {{ @$divisionBudget->division_id == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="name">Nama Anggaran</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') ?? @$divisionBudget->name }}" required>
            </div>

            <div class="form-group">
                <label for="amount">Jumlah Anggaran</label>
                <input type="text" class="form-control"  id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                <input type="hidden" id="amount" name="amount" name="name"  value="{{ old('amount') ?? @$divisionBudget->amount }}">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">{{ @$divisionBudget ? 'Ubah' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script>
    $(document).ready(function () 
    {
        let amount = document.getElementById("amount").value;
        if (amount) 
        {
            document.getElementById("amount_show").value = amount;
            formatRupiahFormat(document.getElementById("amount_show"),"amount"); // Format default value
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
            input.value = '';
            numStr = 0;
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = 'Rp '+rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }
</script>
@stop
