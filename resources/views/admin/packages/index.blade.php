@extends('adminlte::page')

@section('title', 'Packages - ' . $software->nama_software)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Paket: {{ $software->nama_software }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('software.index') }}">Software</a></li>
                <li class="breadcrumb-item"><a href="{{ route('software.show', $software->id) }}">{{ $software->nama }}</a></li>
                <li class="breadcrumb-item active">Paket</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Package List</h3>
            <div class="card-tools">
                @canAccess('create', 'software_packages')
                <a href="{{ route('software.packages.create', $software->id) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Add Package
                </a>
                @endcanAccess
                <a href="{{ route('software.show', $software->id) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Software
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="packages-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Paket</th>
                            <th class="text-center" width="15%">Durasi</th>
                            <th class="text-right" width="15%">Harga</th>
                            <th class="text-center" width="10%">Status</th>
                            <th class="text-center" width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $index => $package)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $package->nama_paket }}</strong>
                                    @if($package->description)
                                        <br><small class="text-muted">{{ Str::limit($package->description, 50) }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $package->durasi_hari }} hari</span>
                                    <br><small class="text-muted">({{ $package->duration_in_months }} bulan)</small>
                                </td>
                                <td class="text-right">
                                    <strong>Rp {{ number_format($package->harga, 0, ',', '.') }}</strong>
                                    <br><small class="text-muted">{{ $package->formatted_price }}</small>
                                </td>
                                <td class="text-center">
                                    {!! $package->status_badge !!}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        @canAccess('update', 'software_packages')
                                        <a href="{{ route('software.packages.edit', [$software->id, $package->id]) }}" 
                                           class="btn btn-sm btn-info mr-1 mb-1"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcanAccess
                                        @canAccess('toggleStatus', 'software_packages')
                                        <button type="button" 
                                                class="btn btn-sm btn-{{ $package->status == 'active' ? 'warning' : 'success' }} toggle-status mr-1 mb-1" 
                                                data-id="{{ $package->id }}"
                                                title="Toggle Status">
                                            <i class="fas fa-{{ $package->status == 'active' ? 'ban' : 'check' }}"></i>
                                        </button>
                                        @endcanAccess
                                        @canAccess('destroy', 'software_packages')
                                        <form action="{{ route('software.packages.destroy', [$software->id, $package->id]) }}" 
                                              method="POST" 
                                              class="delete-form"
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger mr-1 mb-1"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcanAccess
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No packages yet.</p>
                                        <a href="{{ route('software.packages.create', $software->id) }}" 
                                           class="btn btn-success">
                                            <i class="fas fa-plus"></i> Add First Package
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This package will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Toggle status
    $('.toggle-status').on('click', function() {
        const packageId = $(this).data('id');
        const softwareId = "{{ $software->id }}";
        const button = $(this);
        const url = "{{ route('software.packages.toggleStatus', ['software' => ':softwareId', 'package' => ':packageId']) }}".replace(':softwareId', softwareId).replace(':packageId', packageId);
            
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error('Failed to update status');
                }
            },
            error: function() {
                toastr.error('An error occurred');
            }
        });
    });
});
</script>
@stop
