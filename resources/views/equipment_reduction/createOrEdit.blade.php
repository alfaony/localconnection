@extends('adminlte::page')

@section('content_header')
    <h1>{{ isset($reduction) ? 'Ubah Perlengkapan Keluar' : 'Tambah Perlengkapan Keluar' }}</h1>
@stop
@section('content')
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
<div class="container">
    <form action="{{ isset($reduction) ? route('equipment-reduction.update', $reduction->slug) : route('equipment-reduction.store') }}" method="POST">
        @csrf
        @if(isset($reduction))
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="date">Tanggal</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ $reduction->date ?? '' }}" required>
        </div>

        <div class="form-group">
            <label for="reduction_id">Status</label>
            <select class="form-control" id="reduction_id" name="reduction_id">
                @foreach ($reductions as $type)
                    <option value="{{ $type->id }}" {{ (isset($reduction) && $reduction->reduction_id == $type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="equipment_id">Perlengkapan</label>
            <select class="form-control" id="equipment_id" name="equipment_id">
                @foreach ($equipments as $equipment)
                    <option value="{{ $equipment->id }}" data-totalstock="{{ $equipment->total_stock }}" {{ (isset($reduction) && $reduction->equipment_id == $equipment->id) ? 'selected' : '' }}>
                        {{ $equipment->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Stock fields updated with proper IDs -->
        <div class="form-group">
            <label for="current_stock">Stok Saat Ini</label>
            <input type="number" class="form-control" id="current_stock" value="{{ $reduction->stock ?? '' }}" readonly>
        </div>
        <div class="form-group">
            <label for="used_stock">Stok Digunakan</label>
            <input type="number" class="form-control" id="used_stock" name="stock" value="{{ $reduction->stock ?? '' }}" required>
            <input type="hidden" class="form-control" id="used_stock_saved" value="{{ $reduction->stock ?? 0 }}" required>
        </div>
        <div class="form-group">
            <label for="remaining_stock">Jumlah Stok</label>
            <input type="number" class="form-control" id="remaining_stock" value="{{ $reduction->remaining_stock ?? '' }}" readonly>
        </div>

        <div class="form-group">
            <label for="report">Laporan</label>
            <input type="text" class="thriveEditor form-control" id="description_report" data-ids="report"  name="report" value="{{ $reduction->report ?? '' }}">
        </div>

        <div class="form-group">
            <label for="found">Temuan</label>
            <input type="text" class="thriveEditor form-control" id="description_found" data-ids="found"  name="found" value="{{ $reduction->found ?? '' }}">
        </div>

        <div class="form-group">
            <label for="doing">Tindakan</label>
            <input type="text" class="thriveEditor form-control" id="description_doing" data-ids="doing"  name="doing" value="{{ $reduction->doing ?? '' }}">
        </div>

        <button type="submit" id="create_button" class="btn btn-primary">{{ isset($reduction) ? 'Update' : 'Create' }}</button>
    </form>
</div>
@endsection
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
$(document).ready(function() {
    // Fungsi untuk mengupdate field stock saat mengganti pilihan perlengkapan
    function updateStockFields() {
        var totalStock = $('#equipment_id').find(':selected').data('totalstock');
        var usedStockSaved = parseInt($('#used_stock_saved').val()) || 0;

        $('#current_stock').val(totalStock + usedStockSaved);
        calculateRemainingStock();
    }

    // Fungsi untuk menghitung stock yang tersisa dan menangani kondisi stock < 0
    function calculateRemainingStock() {
        var currentStock = parseInt($('#current_stock').val()) || 0;
        var usedStockSaved = parseInt($('#used_stock_saved').val()) || 0;
        var usedStock = parseInt($('#used_stock').val()) || 0;
        var remainingStock = currentStock - usedStock;

        $('#remaining_stock').val(remainingStock);

        // Cek jika remaining stock < 0, tampilkan alert dan disable tombol create
        if (remainingStock < 0) {
            alert('Jumlah stock tidak mencukupi!');
            $('#create_button').prop('disabled', true);
        } else {
            $('#create_button').prop('disabled', false);
        }
    }

    // Event listener untuk perubahan pilihan perlengkapan
    $('#equipment_id').change(function() {
        updateStockFields();
    });

    // Event listener untuk perubahan nilai stock yang digunakan
    $('#used_stock').on('input', function() {
        calculateRemainingStock();
    });

    // Inisialisasi nilai awal
    updateStockFields();
});
</script>
@stop
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

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

