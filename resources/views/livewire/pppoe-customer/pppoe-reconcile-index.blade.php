<div>
    <div class="row">
        @include('components.alert')
    </div>

    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">Reconcile Customer PPPoE</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Scan PPP secret yang sudah ada di router (biasanya dibuat manual
                sebelum pakai sistem ini), lalu hubungkan ke customer yang sudah
                terdaftar atau buat customer baru. Password secret yang sudah ada
                <strong>tidak akan diubah/direset</strong> oleh proses ini.
            </p>

            <div class="row mb-3">
                <div class="col-md-5">
                    <select wire:model="routerId" class="form-control">
                        <option value="">- Pilih Router -</option>
                        @foreach ($routers as $router)
                            <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->host }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button wire:click="scan" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="scan"><i class="fas fa-search mr-1"></i> Scan PPP Secret</span>
                        <span wire:loading wire:target="scan"><i class="fas fa-spinner fa-spin mr-1"></i> Scanning...</span>
                    </button>
                </div>
            </div>

            @if ($scanError)
                <div class="alert alert-danger">{{ $scanError }}</div>
            @endif

            @if (!empty($entries))
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Profile</th>
                            <th>Status Secret</th>
                            <th>Paket Tersugesti</th>
                            <th>Status di Sistem</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                        <tr>
                            <td>{{ $entry['username'] }}</td>
                            <td>{{ $entry['profile'] ?? '-' }}</td>
                            <td>
                                @if ($entry['disabled'])
                                    <span class="badge bg-secondary">Disabled</span>
                                @else
                                    <span class="badge bg-success">Enabled</span>
                                @endif
                            </td>
                            <td>
                                @if ($entry['suggested_package_name'])
                                    <span class="badge bg-info">{{ $entry['suggested_package_name'] }}</span>
                                @else
                                    <span class="text-muted">Tidak ada mapping profile</span>
                                @endif
                            </td>
                            <td>
                                @if ($entry['matched_customer_id'])
                                    <span class="badge bg-success">Terhubung</span>
                                    —
                                    <a href="{{ route('internet-customer.show', $entry['matched_customer_id']) }}">
                                        {{ $entry['matched_customer_name'] }}
                                    </a>
                                    <small class="text-muted">({{ $entry['matched_customer_status'] }})</small>
                                @else
                                    <span class="badge bg-warning">Belum terhubung</span>
                                @endif
                            </td>
                            <td>
                                @if (!$entry['matched_customer_id'])
                                    <button
                                        wire:click="openLinkModal('{{ $entry['username'] }}')"
                                        class="btn btn-info btn-sm mb-1">
                                        <i class="fas fa-link mr-1"></i> Hubungkan
                                    </button>
                                    <button
                                        wire:click="openCreateModal('{{ $entry['username'] }}', {{ $entry['suggested_package_id'] ?? 'null' }})"
                                        class="btn btn-success btn-sm mb-1">
                                        <i class="fas fa-user-plus mr-1"></i> Buat Customer Baru
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif ($routerId && !$scanning)
                <p class="text-muted">Klik "Scan PPP Secret" untuk mulai.</p>
            @endif
        </div>
    </div>

    {{-- Modal: Hubungkan ke Customer Existing --}}
    <div class="modal fade" id="pppoeLinkModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hubungkan ke Customer</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Username PPPoE: <strong>{{ $linkingUsername }}</strong></p>

                    <div class="form-group">
                        <label>Pilih Customer</label>
                        <select wire:model="selectedCustomerId" class="form-control">
                            <option value="">- Pilih Customer -</option>
                            @foreach ($this->candidateCustomers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                        @error('selectedCustomerId') <span class="text-danger">{{ $message }}</span> @enderror
                        <small class="form-text text-muted">
                            Cuma nampilin customer PPPoE yang belum punya username.
                        </small>
                    </div>

                    <div class="alert alert-info mb-0">
                        Password secret yang sudah ada di router <strong>tidak akan diubah</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" wire:click="confirmLink" class="btn btn-primary">Hubungkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Buat Customer Baru --}}
    <div class="modal fade" id="pppoeCreateModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Customer Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Username PPPoE: <strong>{{ $creatingUsername }}</strong></p>

                    <div class="form-group">
                        <label>Nama Customer (sementara)</label>
                        <input type="text" wire:model="newCustomerName" class="form-control">
                        @error('newCustomerName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Paket Internet (PPPoE)</label>
                        <select wire:model="newPackageId" class="form-control">
                            <option value="">- Pilih Paket -</option>
                            @foreach ($this->pppoePackages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }} — Rp{{ number_format($pkg->price) }}</option>
                            @endforeach
                        </select>
                        @error('newPackageId') <span class="text-danger">{{ $message }}</span> @enderror
                        <small class="form-text text-muted">
                            Sudah otomatis dipilih kalau ada mapping profile PPP ke paket — cek dulu sebelum simpan.
                        </small>
                    </div>

                    <div class="alert alert-info">
                        Customer akan dibuat dengan status <strong>PENDING</strong>, terhubung ke secret PPPoE yang
                        sudah ada (password TIDAK diubah). Kamu akan diarahkan ke halaman edit untuk lengkapi data
                        (alamat, KTP, dll) sebelum diaktifkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" wire:click="confirmCreate" class="btn btn-success">Buat Customer</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('open-link-modal', () => $('#pppoeLinkModal').modal('show'));
        Livewire.on('close-link-modal', () => $('#pppoeLinkModal').modal('hide'));
        Livewire.on('open-create-modal', () => $('#pppoeCreateModal').modal('show'));
    });
</script>
@endpush
