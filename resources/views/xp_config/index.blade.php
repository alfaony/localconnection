@extends('adminlte::page')

@section('title', 'Master XP Config')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark">⚡ Master XP Config</h1>
            <small class="text-muted">Kelola konfigurasi poin XP untuk setiap company</small>
        </div>
        <div>
            <a href="{{ route('xp-config.assign') }}" class="btn btn-outline-info btn-sm mr-2">
                <i class="fas fa-link mr-1"></i> Assign ke Company
            </a>
            @canAccess('store','xp_configs')
            <a href="{{ route('xp-config.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle mr-1"></i> Buat Config Baru
            </a>
            @endcanAccess
        </div>
    </div>
@stop

@section('content')
@include('components.alert')

<div class="row">
    @forelse($configs as $config)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-left-{{ $config->is_enabled ? 'success' : 'secondary' }}" style="border-left: 4px solid {{ $config->is_enabled ? '#28a745' : '#adb5bd' }};">
            <div class="card-body">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-title mb-0 font-weight-bold">{{ $config->name }}</h5>
                        <small class="text-muted">{{ $config->description ?? 'Tidak ada deskripsi' }}</small>
                    </div>
                    <span class="badge badge-{{ $config->is_enabled ? 'success' : 'secondary' }} badge-pill px-3 py-1">
                        {{ $config->is_enabled ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                {{-- Info companies --}}
                <div class="d-flex align-items-center mb-3 p-2 rounded" style="background: #f8f9fa;">
                    <i class="fas fa-building text-primary mr-2"></i>
                    <span class="text-sm">
                        Digunakan oleh <strong>{{ $config->companies_count }}</strong> company
                    </span>
                </div>

                {{-- Model XP list --}}
                <div class="mb-3">
                    <small class="text-muted text-uppercase font-weight-bold" style="letter-spacing:.5px;">Nilai XP</small>
                    <div class="mt-2" style="max-height: 150px; overflow-y: auto;">
                        @foreach($config->models as $m)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <small class="text-dark">{{ $m->label ?? $m->source_type }}</small>
                            <span class="badge badge-{{ $m->xp > 0 ? 'primary' : 'danger' }} badge-pill">
                                {{ $m->xp > 0 ? '+' : '' }}{{ $m->xp }} ⚡
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end mt-2">
                    @canAccess('edit','xp_configs')
                    <a href="{{ route('xp-config.edit', $config) }}" class="btn btn-sm btn-outline-primary mr-2">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endcanAccess
                    @canAccess('destroy','xp_configs')
                    <form action="{{ route('xp-config.destroy', $config) }}" method="POST" class="d-inline delete-form">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                    @endcanAccess
                </div>
            </div>
            <div class="card-footer text-muted text-xs bg-transparent">
                Dibuat {{ $config->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-bolt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada XP Config</h5>
                <p class="text-muted">Buat konfigurasi XP pertama Anda untuk mulai gamifikasi karyawan.</p>
                <a href="{{ route('xp-config.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Buat Config Baru
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($configs->hasPages())
<div class="d-flex justify-content-center mt-2">
    {{ $configs->links('vendor.pagination.bootstrap-4') }}
</div>
@endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    @if (session('store'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'XP Config berhasil dibuat.', timer: 2000, showConfirmButton: false });
    @endif
    @if (session('update'))
        Swal.fire({ icon: 'success', title: 'Diperbarui!', text: 'XP Config berhasil diperbarui.', timer: 2000, showConfirmButton: false });
    @endif
    @if (session('delete'))
        Swal.fire({ icon: 'success', title: 'Dihapus!', text: 'XP Config berhasil dihapus.', timer: 2000, showConfirmButton: false });
    @endif
    @if (session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' });
    @endif

    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Config ini?',
                text: 'Config yang masih dipakai company tidak bisa dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    });
</script>
@stop
