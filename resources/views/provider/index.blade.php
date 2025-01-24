@extends('adminlte::page')

@section('title', 'Manage Providers')

@section('content_header')
<h1>Manage Providers</h1>
@stop

@section('content')
<div class="row">
    @canAccess('create', 'providers')
    @canAccess('edit', 'providers')
    <!-- Card Form Create/Edit -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title" id="formTitle">Tambah Provider</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <!-- Form Create/Edit -->
                <form
                    action="{{ isset($provider) ? route('provider.update', $provider->id) : route('provider.store') }}"
                    method="POST">
                    @csrf
                    @if(isset($provider))
                    @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" name="name" id="name" class="form-control"
                            value="{{ old('name', $provider->name ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description"
                            class="form-control">{{ old('description', $provider->description ?? '') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="contact_info" class="form-label">Kontak Informasi</label>
                        <input type="text" name="contact_info" id="contact_info" class="form-control"
                            value="{{ old('contact_info', $provider->contact_info ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="{{ old('email', $provider->email ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label for="service_types" class="form-label">Tipe Layanan & Faktor Volumetrik</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Tipe Layanan</th>
                                    <th style="width: 50%;">Faktor Volumetrik</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceTypes as $serviceType)
                                <tr>
                                    <td>
                                        <input type="hidden" name="service_types[]" value="{{ $serviceType->id }}">
                                        {{ $serviceType->name }}
                                    </td>
                                    <td>
                                        @if(@$provider->serviceTypes)
                                        @foreach($provider->serviceTypes as $providerServiceType)
                                        @if($providerServiceType->pivot->service_type_id == $serviceType->id)
                                        <input type="number" step="0.01" class="form-control"
                                            name="factor_volumetric[{{ $serviceType->id }}]"
                                            value="{{ $providerServiceType->pivot->service_type_id == $serviceType->id ? $providerServiceType->pivot->factor_volumetric : '' }}"
                                            placeholder="Masukkan faktor volumetrik">
                                        @endif
                                        @endforeach
                                        @else
                                        <input type="number" step="0.01" class="form-control"
                                            name="factor_volumetric[{{ $serviceType->id }}]"
                                            value=""
                                            placeholder="Masukkan faktor volumetrik" \>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('provider.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary ml-2">
                            <i class="fas fa-save"></i> {{ isset($provider) ? 'Simpan Perubahan' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcanAccess
    @endcanAccess

    <!-- Card List Providers -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title">Daftar Providers</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Kontak Informasi</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($providers as $provider)
                        <tr>
                            <td>{{ $loop->iteration + ($providers->currentPage() - 1) * $providers->perPage() }}</td>
                            <td>{{ $provider->name }}</td>
                            <td>{{ $provider->description ?? '-' }}</td>
                            <td>{{ $provider->contact_info ?? '-' }}</td>
                            <td>{{ $provider->email ?? '-' }}</td>
                            <td>
                                @canAccess('edit', 'providers')
                                <a href="{{ route('provider.edit', $provider->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcanAccess
                                @canAccess('destroy', 'providers')
                                <form action="{{ route('provider.destroy', $provider->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Anda yakin ingin menghapus provider ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcanAccess
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data provider.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $providers->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.form-label {
    font-weight: bold;
}

.card-header {
    padding: 0.75rem 1.25rem;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Populate the form with the provider's data for editing
    $('.editProvider').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');

        $('#formTitle').text('Edit Provider');
        $('#providerForm').attr('action', `/providers/${id}`);
        $('#providerForm').append('<input type="hidden" name="_method" value="PUT">');
        $('#name').val(name);
        $('#description').val(description);
    });

});
</script>
@stop