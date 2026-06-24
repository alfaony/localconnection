<form wire:submit.prevent="submitInstallationData">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="font-weight-bold">
                    Username <span class="text-danger">*</span>
                </label>
                <input type="text"
                       class="form-control @error('cust_username') is-invalid @enderror"
                       wire:model.defer="cust_username"
                       placeholder="Contoh: pelanggan001">
                @error('cust_username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Username untuk koneksi PPPoE Anda</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="font-weight-bold">
                    Password <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password"
                           class="form-control @error('cust_pass_hash') is-invalid @enderror"
                           wire:model.defer="cust_pass_hash"
                           id="cust_pass_hash_input"
                           placeholder="Password koneksi Anda">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button"
                                onclick="toggleCustPassword()">
                            <i class="fas fa-eye" id="cust-pass-eye-icon"></i>
                        </button>
                    </div>
                </div>
                @error('cust_pass_hash')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="font-weight-bold">Serial Number Perangkat</label>
                <input type="text"
                       class="form-control @error('cust_device_serial_number') is-invalid @enderror"
                       wire:model.defer="cust_device_serial_number"
                       placeholder="Contoh: SN-123456">
                @error('cust_device_serial_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Nomor seri perangkat yang dipasang</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="font-weight-bold">Local Address (IP)</label>
                <input type="text"
                       class="form-control @error('cust_local_address') is-invalid @enderror"
                       wire:model.defer="cust_local_address"
                       placeholder="Contoh: 192.168.1.2">
                @error('cust_local_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Opsional — biarkan kosong jika tidak tahu</small>
            </div>
        </div>
    </div>

    {{-- Foto instalasi dinonaktifkan sementara (soon: tersedia untuk paket premium) --}}
    {{-- <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="font-weight-bold">Foto Instalasi</label>
                <input type="file" class="form-control-file" disabled>
                <small class="text-muted">Segera hadir untuk paket premium 🌟</small>
            </div>
        </div>
    </div> --}}

    <div class="d-flex justify-content-end mt-2">
        <button type="submit"
                class="btn btn-primary px-4"
                wire:loading.attr="disabled"
                wire:target="submitInstallationData">
            <span wire:loading.remove wire:target="submitInstallationData">
                <i class="fas fa-paper-plane mr-1"></i>Kirim Data Instalasi
            </span>
            <span wire:loading wire:target="submitInstallationData">
                <i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...
            </span>
        </button>
    </div>
</form>

@push('scripts')
<script>
function toggleCustPassword() {
    const input   = document.getElementById('cust_pass_hash_input');
    const icon    = document.getElementById('cust-pass-eye-icon');
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endpush
