@extends('adminlte::page')

@section('title', 'Manajemen Zone')

@section('content')
<!-- Notifikasi -->
@include('components.alert')

<!-- Form Tambah/Edit Zone -->
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
                @foreach($zone->sensors as $sensor)
                <div class="sensor-row row mb-2">
                    <div class="col-md-5">
                        <select name="sensors[{{ $loop->index }}][id]" class="form-control sensor-select" required>
                            <option value="">Pilih Sensor</option>
                            @foreach($sensors as $sensorOption)
                            <option value="{{ $sensorOption->id }}" data-type="{{ $sensorOption->type }}"
                                {{ $sensorOption->id == $sensor->id ? 'selected' : '' }}>
                                {{ $sensorOption->name }} ({{ $sensorOption->type }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="sensors[{{ $loop->index }}][value]" class="form-control sensor-value"
                            value="{{ old('sensors.' . $loop->index . '.value', $sensor->pivot->value) }}" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-sensor">❌</button>
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

<!-- Daftar Zone -->
<div class="card shadow">
    <div class="card-header bg-success text-white text-center">
        <h5>Daftar Zone</h5>
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
                        <a href="{{ route('zone.edit', $zone->id) }}" class="btn btn-sm btn-info">
                            ✏️ Edit
                        </a>
                        <form action="{{ route('zone.destroy', $zone->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Hapus Zone ini?')" type="submit"
                                class="btn btn-sm btn-danger">🗑 Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
@section('js')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let sensorCount = {
        {
            isset($zone) ? $zone - > sensors - > count() : 0
        }
    };

    document.getElementById("addSensorBtn").addEventListener("click", function() {
        addSensorRow();
    });

    function addSensorRow() {
        let container = document.getElementById("sensorContainer");
        let row = document.createElement("div");
        row.className = "sensor-row row mb-2";

        row.setAttribute("data-index", sensorCount); // Pastikan semua input memiliki index yang sama

        row.innerHTML = `
            <div class="col-md-3">
                <select name="sensors[${sensorCount}][id]" class="form-control sensor-select" required>
                    <option value="">Pilih Sensor</option>
                    @foreach($sensors as $sensor)
                        <option value="{{ $sensor->id }}" data-type="{{ $sensor->type }}">{{ $sensor->name }} ({{ $sensor->type }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="sensors[${sensorCount}][sensor_code]" class="form-control" placeholder="Masukkan code sensor" required>
            </div>
            <div class="col-md-2">
                <input type="text" name="sensors[${sensorCount}][value]" class="form-control sensor-value" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-sensor"><i class="fa fa-trash"></i></button>
            </div>
        `;

        container.appendChild(row);
        sensorCount++;
        attachEventListeners();
    }

    function attachEventListeners() {
        document.querySelectorAll(".remove-sensor").forEach(button => {
            button.addEventListener("click", function() {
                this.closest(".sensor-row").remove();
            });
        });

        document.querySelectorAll(".sensor-select").forEach(select => {
            select.addEventListener("change", function() {
                let selectedOption = this.options[this.selectedIndex];
                let sensorType = selectedOption.getAttribute("data-type");
                let index = this.closest(".sensor-row").getAttribute("data-index");
                let valueInput = document.querySelector(`[name="sensors[${index}][value]"]`);

                if (sensorType === "integer") {
                    let newInput = document.createElement("input");
                    newInput.type = "number";
                    newInput.className = "form-control sensor-value";
                    newInput.name = `sensors[${index}][value]`;
                    newInput.placeholder = "Masukkan angka";
                    valueInput.replaceWith(newInput);
                } else if (sensorType === "boolean") {
                    let newSelect = document.createElement("select");
                    newSelect.className = "form-control sensor-value";
                    newSelect.name = `sensors[${index}][value]`;
                    newSelect.innerHTML = `
                <option value="true">True</option>
                <option value="false">False</option>
            `;
                    valueInput.replaceWith(newSelect);
                } else {
                    let newInput = document.createElement("input");
                    newInput.type = "text";
                    newInput.className = "form-control sensor-value";
                    newInput.name = `sensors[${index}][value]`;
                    newInput.placeholder = "Masukkan teks";
                    valueInput.replaceWith(newInput);
                }
            });
        });
    }

    attachEventListeners();
});
</script>
@stop