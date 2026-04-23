@extends('adminlte::page')

@section('title', 'Blacklist Pengguna')

@section('content')

<div class="col-md-12">
    @include('components.alert')
</div>

<div class="container p-3 pt-5">

    {{-- ============================================================
         FORM TAMBAH MANUAL
    ============================================================ --}}
    <div class="card card-outline card-danger mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-ban mr-2"></i>Tambah Manual ke Blacklist</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('user-blacklist.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Nama lengkap"
                                value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@contoh.com"
                                value="{{ old('email') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx"
                                value="{{ old('phone') }}"
                                oninput="this.value = this.value.replace(/[^0-9+]/g, '');">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nomor KTP / ID Card</label>
                            <input type="text" name="id_card" class="form-control" placeholder="NIK / No. KTP"
                                value="{{ old('id_card') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alamat</label>
                            <input type="text" name="address" class="form-control" placeholder="Alamat lengkap"
                                value="{{ old('address') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Foto (opsional)</label>
                            <input type="file" name="avatar" class="form-control-file" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Alasan Blacklist</label>
                            <textarea name="reason" class="form-control" rows="2"
                                placeholder="Tulis alasan blacklist...">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-ban mr-1"></i> Tambahkan ke Blacklist
                </button>
            </form>
        </div>
    </div>
        
    {{-- ============================================================
         IMPORT DARI USER TIDAK AKTIF
    ============================================================ --}}

    @canAccess('importInactive','user_blacklists')
    <div class="card card-outline card-warning mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-slash mr-2"></i>Import dari Pengguna Tidak Aktif</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($inactiveUsers->isEmpty())
                <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Tidak ada pengguna tidak aktif.</p>
            @else
                <form action="{{ route('user-blacklist.importInactive') }}" method="POST" id="importForm">
                    @csrf
                    <div class="form-group">
                        <label>Pilih Pengguna Tidak Aktif</label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-secondary" id="selectAllBtn">Pilih Semua</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">Batal Pilih</button>
                        </div>
                        <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40px"></th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inactiveUsers as $u)
                                    <tr class="{{ in_array($u->id, $blacklistedUserIds) ? 'table-secondary' : '' }}">
                                        <td class="text-center align-middle">
                                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                                                class="inactive-user-checkbox"
                                                {{ in_array($u->id, $blacklistedUserIds) ? 'disabled checked' : '' }}>
                                        </td>
                                        <td>
                                            {{ $u->name }}
                                            @if(in_array($u->id, $blacklistedUserIds))
                                                <span class="badge badge-secondary ml-1">Sudah di-blacklist</span>
                                            @endif
                                        </td>
                                        <td>{{ $u->email }}</td>
                                        <td>{{ $u->phone }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alasan Blacklist (opsional)</label>
                        <input type="text" name="reason" class="form-control" placeholder="Misal: Tidak aktif / Resign">
                    </div>
                    <button type="submit" class="btn btn-warning" id="importSubmitBtn">
                        <i class="fas fa-file-import mr-1"></i> Import ke Blacklist
                    </button>
                </form>
            @endif
        </div>
    </div>
    @endcanAccess

    {{-- ============================================================
         DAFTAR BLACKLIST
    ============================================================ --}}
    <div class="card card-outline card-dark">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Daftar Blacklist</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('user-blacklist.index') }}" method="GET" class="mb-3">
                <div class="input-group" style="max-width: 350px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama / email..."
                        value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>

            @if($blacklists->isEmpty())
                <p class="text-muted"><i class="fas fa-check-circle mr-1 text-success"></i> Belum ada data blacklist.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:50px">No</th>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>KTP</th>
                                <th>Alamat</th>
                                <th>Alasan</th>
                                <th>Di-blacklist oleh</th>
                                <th>Tanggal</th>
                                <th style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($blacklists as $i => $bl)
                            <tr>
                                <td>{{ ($blacklists->currentPage() - 1) * $blacklists->perPage() + $i + 1 }}</td>
                                <td class="text-center" style="width:70px;">
                                    @if($bl->avatar)
                                        <img src="{{ s3_asset(true, 10, $bl->avatar) }}"
                                            alt="foto" class="img-circle elevation-2"
                                            style="width:45px;height:45px;object-fit:cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:45px;height:45px;margin:auto;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $bl->name }}</strong>
                                    @if($bl->user_id)
                                        <br><small class="text-muted"><i class="fas fa-link mr-1"></i>User terhubung</small>
                                    @endif
                                </td>
                                <td>{{ $bl->email ?? '-' }}</td>
                                <td>{{ $bl->phone ?? '-' }}</td>
                                <td>{{ $bl->id_card ?? '-' }}</td>
                                <td>{{ $bl->address ?? '-' }}</td>
                                <td>
                                    @if($bl->reason)
                                        <span class="text-danger">{{ $bl->reason }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ optional($bl->blacklistedBy)->name ?? '-' }}</td>
                                <td>{{ $bl->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('user-blacklist.destroy', $bl->id) }}"
                                        onsubmit="return confirm('Hapus dari blacklist?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $blacklists->appends(request()->all())->links() }}
            @endif
        </div>
    </div>

</div>

@section('js')
<script>
// Pilih semua / batal semua
document.getElementById('selectAllBtn')?.addEventListener('click', function () {
    document.querySelectorAll('.inactive-user-checkbox:not(:disabled)').forEach(cb => cb.checked = true);
});
document.getElementById('deselectAllBtn')?.addEventListener('click', function () {
    document.querySelectorAll('.inactive-user-checkbox:not(:disabled)').forEach(cb => cb.checked = false);
});

// Validasi minimal 1 dipilih saat submit import
document.getElementById('importForm')?.addEventListener('submit', function (e) {
    const checked = document.querySelectorAll('.inactive-user-checkbox:checked:not(:disabled)');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu pengguna untuk di-import ke blacklist.');
    }
});
</script>
@endsection

@endsection
