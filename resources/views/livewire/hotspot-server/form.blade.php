<div>
    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">{{ $serverId ? 'Edit' : 'Tambah' }} Hotspot Server</h3>
        </div>
        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Router <span class="text-danger">*</span></label>
                            <select wire:model="router_id" class="form-control @error('router_id') is-invalid @enderror">
                                <option value="">-- Pilih Router --</option>
                                @foreach ($routers as $router)
                                    <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->host }})</option>
                                @endforeach
                            </select>
                            @error('router_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Server <span class="text-danger">*</span></label>
                            <input wire:model.lazy="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Nama hotspot server di MikroTik">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Interface</label>
                            <select wire:model="interface_id" class="form-control @error('interface_id') is-invalid @enderror">
                                <option value="">-- Pilih Interface (opsional) --</option>
                                @foreach ($interfaces as $iface)
                                    <option value="{{ $iface->id }}">{{ $iface->name }}</option>
                                @endforeach
                            </select>
                            @error('interface_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Address Pool</label>
                            <select wire:model="address_pool_id" class="form-control @error('address_pool_id') is-invalid @enderror">
                                <option value="">-- Pilih Pool (opsional) --</option>
                                @foreach ($pools as $pool)
                                    <option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->cidr }})</option>
                                @endforeach
                            </select>
                            @error('address_pool_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hotspot Server Profile (MikroTik)</label>
                            <input wire:model.lazy="profile_name" type="text" class="form-control @error('profile_name') is-invalid @enderror"
                                placeholder="Nama profile di MikroTik (opsional)">
                            @error('profile_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>DNS Name</label>
                            <input wire:model.lazy="dns_name" type="text" class="form-control @error('dns_name') is-invalid @enderror"
                                placeholder="hotspot.domain.com (opsional)">
                            @error('dns_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('hotspot-server.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
