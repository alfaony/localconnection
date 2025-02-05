@extends('adminlte::page')

@section('title', isset($postalCode) ? 'Kode Pos' : 'Kode Pos')

@section('content_header')
    <h1>{{ isset($postalCode) ? 'Kode Pos' : 'Kode Pos' }}</h1>
@stop

@section('content')
@include('components.alert')

@canAccess('store', 'postal_codes')
@canAccess('update', 'postal_codes')
<div class="card">
    <div class="card-body">
        <form action="{{ isset($postalCode) ? route('postal-code.update', $postalCode->id) : route('postal-code.store') }}" method="POST">
            @csrf
            @if(isset($postalCode)) @method('PUT') @endif

            <div class="mb-3">
                <label for="subdistrict_id" class="form-label">Kelurahan</label>
                <select name="subdistrict_id" id="subdistrict_id" class="form-control select2" required>
                    <option value="" disabled selected>Select Kecamatan</option>
                    @foreach($subdistricts as $subdistrict)
                        <option value="{{ $subdistrict->id }}" {{ isset($postalCode) && $postalCode->subdistrict_id == $subdistrict->id ? 'selected' : '' }}>
                            {{ $subdistrict->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="postal_code" class="form-label">Kode Pos</label>
                <input type="text" name="postal_code" id="postal_code" class="form-control" 
                       value="{{ old('postal_code', @$postalCode->postal_code ?? '') }}" required>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('postal-code.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary ml-2">Save</button>
            </div>
        </form>
    </div>
</div>
@endcanAccess
@endcanAccess

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Kode Pos</h3>
    </div>
    <div class="card-body">
        <table id="postalCodesTable" class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Kode Pos</th>
                    <th>Kelurahan</th>
                    <th>Kecamatan</th>
                    <th>Kabupaten / Kota</th>
                    <th>Provinsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            {{-- 
            <tbody>
                @forelse($postalCodes as $index => $postalCode)
                    <tr>
                        <td>{{ $loop->iteration + ($postalCodes->currentPage() - 1) * $postalCodes->perPage() }}</td>
                        <td>{{ $postalCode->postal_code }}</td>
                        <td>{{ $postalCode->subdistrict->name ?? '-' }}</td>
                        <td>{{ $postalCode->subdistrict->district->name ?? '-' }}</td>
                        <td>{{ $postalCode->subdistrict->district->city->name ?? '-' }}</td>
                        <td>{{ $postalCode->subdistrict->district->city->province->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('postal-code.edit', $postalCode->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> 
                            </a>
                            <form action="{{ route('postal-code.destroy', $postalCode->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No postal codes found.</td>
                    </tr>
                @endforelse
            </tbody>
            --}}
        </table>
    </div>
    {{-- 
    <div class="card-footer">
        {{ $postalCodes->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>
    --}}
</div>
@stop

@section('css')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<style>
.select2-selection__rendered {
    line-height: 31px !important;
}

.select2-container .select2-selection--single {
    height: 35px !important;
}

.select2-selection__arrow {
    height: 34px !important;
}

.ql-container {
    min-height: 150px;
    height: auto;
}
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%',
        });
    });
</script>
<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#postalCodesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('postal-code.dataTableJson') }}',
            columns: [
                { data: 'postal_code', name: 'postal_code', orderable: false },
                { data: 'subdistrict.name', name: 'subdistrict.name', orderable: false },
                { data: 'subdistrict.district.name', name: 'subdistrict.district.name', orderable: false },
                { data: 'subdistrict.district.city.name', name: 'subdistrict.district.city.name', orderable: false },
                { data: 'subdistrict.district.city.province.name', name: 'subdistrict.district.city.province.name', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[1, 'asc']],
        });
    });
</script>
@stop