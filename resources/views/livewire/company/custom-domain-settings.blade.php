<div>
    <div class="row">
        @include('components.alert')
    </div>

    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">Domain Company</h3>
        </div>
        <div class="card-body">

            <div class="alert alert-info">
                <strong>URL gratis kamu saat ini:</strong>
                <a href="{{ $company->public_url }}" target="_blank">{{ $company->public_url }}</a>
            </div>

            <hr>

            <h5>Pakai Domain Sendiri (Opsional)</h5>
            <p class="text-muted">
                Contoh: <code>www.nama-isp-kamu.com</code>. Setelah diverifikasi, URL registrasi customer
                kamu bisa diakses lewat domain sendiri.
            </p>

            <div class="form-group">
                <label>Domain</label>
                <div class="input-group">
                    <input type="text" wire:model="domainInput" class="form-control @error('domainInput') is-invalid @enderror"
                           placeholder="www.domainsaya.com">
                    <div class="input-group-append">
                        <button wire:click="submitDomain" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
                @error('domainInput') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            @if ($verificationInstructions)
                <div class="card card-outline card-warning mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Langkah Verifikasi Domain</h3>
                    </div>
                    <div class="card-body">
                        <p>Tambahkan 2 DNS record ini di panel domain kamu (Niagahoster, Cloudflare, dll):</p>

                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr><th>Tipe</th><th>Nama</th><th>Value</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>TXT</td>
                                    <td><code>{{ $verificationInstructions['txt_record_name'] }}</code></td>
                                    <td><code>{{ $verificationInstructions['txt_record_value'] }}</code></td>
                                </tr>
                                <tr>
                                    <td>CNAME</td>
                                    <td><code>{{ $verificationInstructions['domain'] }}</code></td>
                                    <td><code>{{ $verificationInstructions['cname_target'] }}</code></td>
                                </tr>
                            </tbody>
                        </table>

                        <p class="text-muted">
                            TXT record buat verifikasi kepemilikan domain. CNAME buat ngearahin traffic domain
                            kamu ke server kami. Propagasi DNS bisa makan waktu beberapa menit sampai 24 jam.
                        </p>

                        <button wire:click="verify" wire:loading.attr="disabled" class="btn btn-success">
                            <span wire:loading.remove wire:target="verify"><i class="fas fa-check mr-1"></i> Verifikasi Sekarang</span>
                            <span wire:loading wire:target="verify"><i class="fas fa-spinner fa-spin mr-1"></i> Mengecek DNS...</span>
                        </button>

                        @if ($verifyResult === 'failed')
                            <div class="alert alert-danger mt-3 mb-0">
                                TXT record belum ketemu / belum sesuai. Cek lagi DNS record kamu, atau tunggu
                                propagasi DNS-nya (bisa sampai 24 jam untuk beberapa provider domain).
                            </div>
                        @elseif ($verifyResult === 'success')
                            <div class="alert alert-success mt-3 mb-0">
                                ✅ Domain berhasil diverifikasi!
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($company->custom_domain && $company->custom_domain_verified_at)
                <div class="alert alert-success mt-3">
                    <strong>Domain aktif:</strong> {{ $company->custom_domain }}
                    (terverifikasi {{ $company->custom_domain_verified_at->diffForHumans() }})
                    <button wire:click="removeCustomDomain" wire:confirm="Yakin hapus custom domain ini?" class="btn btn-sm btn-outline-danger float-right">
                        Hapus Domain
                    </button>
                </div>
            @endif

            <div class="alert alert-warning mt-3">
                <strong>Catatan penting:</strong> setelah verifikasi TXT record berhasil, butuh proses tambahan
                di sisi server (SSL certificate untuk domain kamu) sebelum domain benar-benar bisa diakses via
                HTTPS. Tim kami akan proses ini setelah verifikasi kamu berhasil.
            </div>
        </div>
    </div>
</div>
