<div>
    <div class="row">
        @include('components.alert')
    </div>
    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">Daftar Hotspot Server</h3>
            <div class="card-tools d-flex gap-2">
                <input wire:model.debounce.400ms="search" type="text" class="form-control form-control-sm" placeholder="Cari nama server...">
                <a href="{{ route('hotspot-server.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i></a>
            </div>
        </div>
        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nama Server</th>
                        <th>Router</th>
                        <th>Interface</th>
                        <th>Address Pool</th>
                        <th>Profile</th>
                        <th>DNS</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($servers as $server)
                    <tr>
                        <td><strong>{{ $server->name }}</strong></td>
                        <td>{{ $server->router->name ?? '-' }}</td>
                        <td>{{ $server->interface->name ?? '-' }}</td>
                        <td>{{ $server->addressPool->name ?? '-' }}</td>
                        <td>{{ $server->profile_name ?? '-' }}</td>
                        <td>{{ $server->dns_name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('hotspot-server.edit', $server->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-danger btn-sm" title="Hapus"
                                onclick="confirm('Hapus server ini?') && @this.delete('{{ $server->id }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada hotspot server.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $servers->links() }}
            </div>
        </div>
    </div>
</div>
