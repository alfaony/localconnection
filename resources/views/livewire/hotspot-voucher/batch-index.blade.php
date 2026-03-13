<div>
    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">Batch Voucher Hotspot</h3>
            <div class="card-tools">
                <button wire:click="toggleForm" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Generate Voucher
                </button>
            </div>
        </div>

        {{-- Form Generate Batch --}}
        @if($showForm)
        <div class="card-body border-bottom bg-light">
            <h5>Generate Batch Voucher</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Hotspot Server <span class="text-danger">*</span></label>
                        <select wire:model.defer="hotspot_server_id" class="form-control @error('hotspot_server_id') is-invalid @enderror">
                            <option value="">-- Pilih Server --</option>
                            @foreach ($servers as $server)
                                <option value="{{ $server->id }}">{{ $server->name }} ({{ $server->router->name ?? '-' }})</option>
                            @endforeach
                        </select>
                        @error('hotspot_server_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Profile Voucher <span class="text-danger">*</span></label>
                        <select wire:model.defer="internet_package_id" class="form-control @error('internet_package_id') is-invalid @enderror">
                            <option value="">-- Pilih Profile --</option>
                            @foreach ($profiles as $profile)
                                <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                            @endforeach
                        </select>
                        @error('internet_package_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Jumlah</label>
                        <input wire:model.defer="quantity" type="number" min="1" max="500" class="form-control @error('quantity') is-invalid @enderror">
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Prefix Username</label>
                        <input wire:model.defer="prefix" type="text" maxlength="10" class="form-control">
                    </div>
                </div>
            </div>
            <button wire:click="generate" class="btn btn-success btn-sm">
                <i class="fas fa-cogs"></i> Generate & Provision
            </button>
            <button wire:click="toggleForm" class="btn btn-secondary btn-sm ml-2">Batal</button>
        </div>
        @endif

        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Server</th>
                        <th>Profile</th>
                        <th>Jumlah</th>
                        <th>Terpakai</th>
                        <th>Sisa</th>
                        <th>Di-generate oleh</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                    <tr>
                        <td>{{ $batch->hotspotServer->name ?? '-' }}</td>
                        <td>{{ $batch->internetPackage->name ?? '-' }}</td>
                        <td>{{ $batch->quantity }}</td>
                        <td><span class="badge bg-warning">{{ $batch->used_count }}</span></td>
                        <td><span class="badge bg-success">{{ $batch->unused_count }}</span></td>
                        <td>{{ $batch->generatedBy->name ?? '-' }}</td>
                        <td>{{ $batch->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('hotspot-voucher.print', $batch->id) }}" target="_blank" class="btn btn-info btn-sm" title="Print PDF">
                                <i class="fas fa-print"></i> Print
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada batch voucher.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $batches->links() }}
        </div>
    </div>
</div>
