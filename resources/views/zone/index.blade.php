@extends('adminlte::page')

@section('title', 'Manajemen Zone')

@section('content')
<!-- Notifikasi -->
@include('components.alert')

<!-- Form Tambah/Edit Zone -->
@canAccess('store','zones')
@canAccess('update','zones')
<div class="card shadow mb-4 mt-3">
    <div class="card-header bg-primary text-white text-center">
        <h5>{{ isset($zone) ? 'Edit Zone' : 'Tambah Zone' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ isset($zone) ? route('zone.update', $zone->id) : route('zone.store') }}" method="POST">
            @csrf
            @if(isset($zone))
            @method('PUT')
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Zone:</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ isset($zone) ? $zone->name : '' }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Warehouse:</label>
                        <select name="warehouse_id" class="form-control" required>
                            @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                {{ isset($zone) && $zone->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pilih Sensor (Dynamically Added) -->
            <div class="form-group mb-3">
                <label class="form-label">Sensor:</label><br>
                <button type="button" class="btn btn-sm btn-success" id="addSensorBtn">➕ Tambah Sensor</button>
            </div>

            <div id="sensorContainer">
                @if(isset($zone) && $zone->sensors->count() > 0)
                @foreach($zone->sensors as $index => $sensor)
                <div class="sensor-row row align-items-center mb-2">
                    <input type="hidden" name="sensors[{{ $index }}][id]" value="{{ $sensor->pivot->id ?? '' }}">
                    <div class="col-md-3">

                        <select name="sensors[{{ $index }}][sensor_id]" class="form-control sensor-select select2"
                            data-index="{{ $index }}" required>
                            <option value="">Pilih Sensor</option>
                            @foreach($sensors as $sensorOption)
                            <option value="{{ $sensorOption->id }}" data-type="{{ $sensorOption->type }}"
                                {{ $sensorOption->id == $sensor->id ? 'selected' : '' }}>
                                {{ $sensorOption->name }} ({{ $sensorOption->type }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="sensors[{{ $index }}][sensor_code]" class="form-control"
                            value="{{ $sensor->pivot->sensor_code }}">
                    </div>
                    <div class="col-md-3 sensor-value-container" id="sensor-value-container-{{ $index }}">
                        @if($sensor->type === 'integer')
                        <input type="number" name="sensors[{{ $index }}][value]" class="form-control"
                            value="{{ $sensor->pivot->value }}" required>
                        @elseif($sensor->type === 'boolean')
                        <select name="sensors[{{ $index }}][value]" class="form-control" required>
                            <option value="true" {{ $sensor->pivot->value == 'true' ? 'selected' : '' }}>True</option>
                            <option value="false" {{ $sensor->pivot->value == 'false' ? 'selected' : '' }}>False
                            </option>
                        </select>
                        @else
                        <input type="text" name="sensors[{{ $index }}][value]" class="form-control"
                            value="{{ $sensor->pivot->value }}" required>
                        @endif
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-sensor"><i
                                class="fa fa-trash"></i></button>
                    </div>
                </div>
                @endforeach
                @endif
            </div>


            <div class="text-right">
                <button type="submit" class="btn btn-primary">
                    {{ isset($zone) ? '💾 Simpan Perubahan' : '➕ Tambah Zone' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endcanAccess
@endcanAccess

<!-- Daftar Zone -->
<div class="card shadow">
    <div class="card-header bg-success text-white">
        <div class="row align-items-center">
            <!-- Bagian Judul -->
            <div class="col-md-9 col-sm-12 text-center text-md-start">
                <h5 class="mb-0">Daftar Zona</h5>
            </div>

            <!-- Bagian Form Pencarian -->
            <div class="col-md-3 col-sm-12">
                <form method="GET" action="{{ route('zone.index') }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search..."
                            value="{{ request('search') }}">
                        <button class="btn btn-light px-3 btn-sm ml-2" type="submit">🔍 Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Nama</th>
                    <th>Warehouse</th>
                    <th>Sensor</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($zones as $zone)
                <tr class="text-center">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $zone->name }}</td>
                    <td>{{ $zone->warehouse->name }}</td>
                    <td>{{ $zone->sensors->pluck('name')->join(', ') }}</td>
                    <td>
                        @canAccess('edit','zones')
                        <a href="{{ route('zone.edit', $zone->id) }}" class="btn btn-sm btn-info">
                            <i class="fa fa-edit"></i>
                        </a>
                        @endcanAccess

                        @canAccess('destroy','zones')
                        <form action="{{ route('zone.destroy', $zone->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Hapus Zone ini?')" type="submit"
                                class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                        </form>
                        @endcanAccess
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();
});
document.addEventListener("DOMContentLoaded", function() {
    let sensorContainer = document.getElementById("sensorContainer");

    document.getElementById("addSensorBtn").addEventListener("click", function() {
        var key = generateRandomString(4);
        addSensorRow(key);
    });

    function addSensorRow(index) {
        let row = document.createElement("div");
        row.className = "sensor-row row align-items-center mb-2";
        row.setAttribute("data-index", index);

        row.innerHTML = `
            <div class="col-md-3">
                <select name="sensors[${index}][sensor_id]" class="form-control sensor-select select2" data-index="${index}" required>
                    <option value="">Pilih Sensor</option>
                    @foreach($sensors as $sensor)
                        <option value="{{ $sensor->id }}" data-type="{{ $sensor->type }}">{{ $sensor->name }} ({{ $sensor->type }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="sensors[${index}][sensor_code]" class="form-control" placeholder="Masukkan kode sensor">
            </div>
            <div class="col-md-3 sensor-value-container" id="sensor-value-container-${index}">
                <!-- Nilai sensor akan diinject di sini -->
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-sm btn-danger remove-sensor"><i class="fa fa-trash"></i></button>
            </div>
        `;

        sensorContainer.appendChild(row);
        $(".select2").select2(); // **Re-inisialisasi Select2**
    }

    // **Event Delegation untuk Select2**
    $(document).on("select2:select", ".sensor-select", function() {

        let selectedOption = this.options[this.selectedIndex];
        let sensorType = selectedOption.getAttribute("data-type");
        let index = this.getAttribute("data-index"); // Ambil index yang benar
        let valueContainer = document.getElementById(`sensor-value-container-${index}`);

        console.log("Sensor Type:", sensorType, "Index:", index, "Value Container:", valueContainer);

        valueContainer.innerHTML = ""; // Reset isi sebelumnya

        if (sensorType === "integer") {
            valueContainer.innerHTML = `
                <input type="number" name="sensors[${index}][value]" class="form-control sensor-value" placeholder="Masukkan angka" required>
            `;
        } else if (sensorType === "boolean") {
            valueContainer.innerHTML = `
                <select name="sensors[${index}][value]" class="form-control sensor-value select2" required>
                    <option value="true">True</option>
                    <option value="false">False</option>
                </select>
            `;
            $(".select2").select2(); // Pastikan Select2 tetap berfungsi setelah inject HTML
        } else {
            valueContainer.innerHTML = `
                <input type="text" name="sensors[${index}][value]" class="form-control sensor-value" placeholder="Masukkan teks" required>
            `;
        }
    });

    // **Event Delegation untuk hapus sensor**
    $(document).on("click", ".remove-sensor", function() {
        $(this).closest(".sensor-row").remove();
    });
});

// **Fungsi Generate Random String**
function generateRandomString(length) {
    var result = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}
</script>
@stop
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
}

.container {
    background-color: #fff;
    border-radius: 5px;
}

.select2-selection__rendered {
    line-height: 31px !important;
}

.select2-container .select2-selection--single {
    height: 35px !important;
}

.select2-selection__arrow {
    height: 34px !important;
}
</style>
@stop