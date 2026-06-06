<div>
    <div class="row">
        @include('components.alert')
    </div>
    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">Daftar Router - Mikrotik</h3>
            @canAccess('create','routers')
            <div class="card-tools">
                <a href="{{ route('router.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i></a>
            </div>
            @endcanAccess
        </div>
        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Pop</th>
                        <th>Name</th>
                        <th>Host</th>
                        <th>Port</th>
                        <th>Username</th>
                        <th>SSL</th>
                        <th>Active</th>
                        <th>Pelanggan</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mikrotiks as $mikrotik)
                    <tr>
                        <td>{{ $mikrotik->pop ? $mikrotik->pop->name : '-' }}</td>
                        <td>{{ $mikrotik->name }}</td>
                        <td>{{ $mikrotik->host }}</td>
                        <td>{{ $mikrotik->port }}</td>
                        <td>{{ $mikrotik->username }}</td>
                        <td>{!! $mikrotik->ssl ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                        <td>
                            @switch($mikrotik->active_status)
                                @case('UP')
                                    <span class="badge bg-success">UP</span>
                                    @break
                                @case('DOWN')
                                    <span class="badge bg-danger">DOWN</span>
                                    @break
                                @default
                                    <span class="badge bg-warning">{{ $mikrotik->active_status }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            @if($mikrotik->internet_customers_count > 0)
                                <span class="badge badge-info">{{ $mikrotik->internet_customers_count }} pelanggan</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @canAccess('mapping','routers')
                            <a href="{{ route('router.mapping', $mikrotik) }}" class="btn btn-secondary btn-sm mb-1" title="Mapping Paket Profile"><i class="fas fa-sitemap"></i></a>
                            @endcanAccess

                            @canAccess('show','routers')
                            <a href="{{ route('router.show', $mikrotik) }}" class="btn btn-primary btn-sm mb-1" title="Detail Router"><i class="fas fa-eye"></i></a>
                            @endcanAccess

                            @canAccess('edit','routers')
                            <a href="{{ route('router.edit', $mikrotik) }}" class="btn btn-warning btn-sm mb-1" title="Edit Router"><i class="fas fa-edit"></i></a>
                            @endcanAccess

                            @canAccess('destroy','routers')
                            @if($mikrotik->internet_customers_count > 0)
                                <span title="Tidak dapat dihapus — masih memiliki {{ $mikrotik->internet_customers_count }} pelanggan"
                                      data-toggle="tooltip">
                                    <button type="button" class="btn btn-danger btn-sm mb-1" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </span>
                            @else
                                <button type="button" class="btn btn-danger btn-sm mb-1"
                                        onclick="confirm('Yakin ingin menghapus router ini?') && @this.delete({{ $mikrotik->id }})"
                                        title="Hapus Router">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                            @endcanAccess
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-4">
                {{ $mikrotiks->links() }}
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    window.addEventListener('toast', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({
            icon: event.detail.type,
            title: event.detail.message
        });
    });

    // Aktifkan tooltip pada elemen yang ada data-toggle="tooltip"
    document.addEventListener('livewire:load', () => initTooltips());
    document.addEventListener('livewire:update', () => initTooltips());
    function initTooltips() {
        $('[data-toggle="tooltip"]').tooltip({ boundary: 'window' });
    }
</script>
@endpush