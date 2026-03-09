@extends('adminlte::page')

@section('title', 'Software Management')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Software Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active">Software</li>
            </ol>
        </div>
    </div>
    @stop
    
@section('content')
@include('components.alert')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    @canAccess('importStatus', 'software')
                    @canAccess('importTemplate', 'software')
                    @canAccess('import', 'software')
                    <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#importModal">
                        <i class="fas fa-file-excel"></i> Import Software
                    </button>
                    @endcanAccess
                    @endcanAccess
                    @endcanAccess

                    @canAccess('create', 'software')
                    <a href="{{ route('software.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Software
                    </a>
                    @endcanAccess
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Filter --}}
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari software..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('software.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover" id="softwaresTable">
                    <thead>
                        <tr>
                            <th width="80">Logo</th>
                            <th>Nama Software</th>
                            <th>Tipe Paket</th>
                            <th>Packages</th>
                            <th>Master Accounts</th>
                            @canAccess('toggleStatus', 'software')
                            <th>Status</th>
                            @endcanAccess
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($softwares as $software)
                        <tr>
                            <td>
                                @if($software->logo)
                                <img src="{{ s3_asset(true,10,$software->logo) }}" alt="{{ $software->nama }}" class="img-thumbnail" style="max-width: 60px;">
                                @else
                                <div class="bg-secondary text-white text-center" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="fas fa-image"></i>
                                </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $software->nama }}</strong>
                                <br>
                                <small class="text-muted">{{ $software->slug }}</small>
                                <br>
                                <small class="text-muted">{{ $software->pic->name ?? "" }}</small>
                                
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $software->tipe_paket }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('software.packages.index', $software) }}" class="badge badge-primary">
                                    {{ $software->packages->count() }} packages
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success">
                                    {{ $software->masterAccounts->count() }} accounts
                                </span>
                            </td>
                            @canAccess('toggleStatus', 'software')
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input toggle-status" 
                                           id="status-{{ $software->id }}" 
                                           data-id="{{ $software->id }}"
                                           {{ $software->status == 'active' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="status-{{ $software->id }}">
                                        <span class="badge badge-{{ $software->status == 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($software->status) }}
                                        </span>
                                    </label>
                                </div>
                            </td>
                            @endcanAccess
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @canAccess('show', 'software')
                                    <a href="{{ route('software.show', $software) }}" class="btn btn-info mb-1 mr-1" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcanAccess
                                    @canAccess('edit', 'software')
                                    <a href="{{ route('software.edit', $software) }}" class="btn btn-warning mb-1 mr-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcanAccess
                                    @canAccess('destroy', 'software')
                                    <button type="button" class="btn btn-danger btn-delete mb-1 mr-1" 
                                            data-id="{{ $software->id }}"
                                            data-name="{{ $software->nama }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcanAccess
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada data software</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $softwares->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- Delete Form (Hidden) --}}
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Software</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        @canAccess('importTemplate', 'software')
                        <div class="form-group">
                            <label>Download Template</label>
                            <p>Silakan gunakan template berikut untuk memastikan format data benar.</p>
                            <a href="{{ route('software.import.template') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-download"></i> Download Template CSV
                            </a>
                        </div>
                        @endcanAccess
                        <hr>
                        <div class="form-group">
                            <label for="importFile">Upload File (CSV, Max 5MB)</label>
                            <input type="file" class="form-control-file" id="importFile" name="file" accept=".csv" required>
                        </div>
                    </form>

                    {{-- Progress Area --}}
                    <div id="importProgressArea" style="display: none;" class="mt-4">
                        <h6 id="importStatusText" class="text-primary text-center mb-2">Memproses...</h6>
                        <div class="progress">
                            <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        
                        {{-- Log --}}
                        <div id="importErrorLog" class="alert alert-danger mt-3" style="display: none;">
                            <ul id="importErrorList" class="mb-0 pl-3"></ul>
                        </div>
                    </div>
                </div>
                @canAccess('import','software')
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnProsesImport">Proses Import</button>
                    <button type="button" class="btn btn-warning" id="btnReloadImport" style="display: none;" onclick="checkImportStatus()">Cek Status Final</button>
                </div>
                @endcanAccess
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
<script>
$(document).ready(function() {
    // Toggle Status
    $('.toggle-status').on('change', function() {
        const id = $(this).data('id');
        const isChecked = $(this).is(':checked');
        let url = "{{ route('software.toggleStatus', ':id') }}".replace(':id', id);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    // Update badge
                    const badge = $(`#status-${id}`).siblings('label').find('.badge');
                    if(response.status === 'active') {
                        badge.removeClass('badge-danger').addClass('badge-success').text('Active');
                    } else {
                        badge.removeClass('badge-success').addClass('badge-danger').text('Inactive');
                    }
                }
            },
            error: function() {
                toastr.error('Gagal mengupdate status');
                // Revert checkbox
                $(`#status-${id}`).prop('checked', !isChecked);
            }
        });
    });

    // Delete Button
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const url = `{{ route('software.destroy', ':id') }}`.replace(':id', id);
        
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            html: `Software <strong>${name}</strong> akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('#delete-form');
                form.attr('action', url);
                form.submit();
            }
        });
    });

    // ============================================
    // IMPORT FUNCTIONALITY & WEBSOCKET BROADCASTING
    // ============================================
    const userId = @json(auth()->id());
    const host   = '{{ config('services.connection_reverb.host') }}';
    const key    = '{{ config('services.connection_reverb.key') }}';
    const port   = '{{ config('services.connection_reverb.port') }}';

    window.Pusher = Pusher;
    window.Echo = new Echo.default({
        broadcaster       : 'reverb',
        key               : key,
        wsHost            : host,
        wsPort            : 8080,
        wssPort           : port,
        forceTLS          : true,
        enabledTransports : ['ws', 'wss'],
        authEndpoint      : '/broadcasting/authorize',
        disableStats      : true,
    });

    // Listen to broadcast events
    window.Echo.channel(`bos.user.${userId}`)
        .listen('.software.import.status', (e) => {
            console.log("Import broadcast received:", e);
            if (e.status === 'progress') {
                $('#importStatusText').text(e.message);
                $('#importProgressBar')
                    .css('width', e.percent + '%')
                    .attr('aria-valuenow', e.percent)
                    .text(e.percent + '%');
            } else if (e.status === 'completed') {
                $('#importStatusText').removeClass('text-primary').addClass('text-success').text(e.message);
                $('#importProgressBar')
                    .css('width', '100%')
                    .attr('aria-valuenow', 100)
                    .text('100%')
                    .removeClass('progress-bar-animated');
                
                $('#btnProsesImport').hide();
                $('#btnReloadImport').hide();

                if (e.failed_count > 0 && e.errors && e.errors.length > 0) {
                    $('#importErrorLog').show();
                    const list = $('#importErrorList');
                    list.empty();
                    e.errors.forEach(err => {
                        list.append(`<li>${err}</li>`);
                    });
                }
                
                setTimeout(() => {
                    toastr.success(e.message);
                    window.location.reload();
                }, 3000);
            } else if (e.status === 'error') {
                $('#importStatusText').removeClass('text-primary').addClass('text-danger').text(e.message);
                $('#importProgressBar')
                    .removeClass('bg-success progress-bar-animated').addClass('bg-danger');
                $('#btnProsesImport').show().prop('disabled', false);
                $('#btnReloadImport').hide();
                toastr.error(e.message);
            }
        });

    // Handle initial ajax call to trigger import job
    $('#btnProsesImport').on('click', function() {
        var formData = new FormData($('#importForm')[0]);
        var fileInput = $('#importFile')[0];
        
        if (fileInput.files.length === 0) {
            toastr.warning('Pilih file CSV terlebih dahulu');
            return;
        }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        $('#importProgressArea').show();
        $('#importStatusText').removeClass('text-success text-danger').addClass('text-primary').text('Mengupload file...');
        $('#importProgressBar').css('width', '10%').text('10%').addClass('progress-bar-animated bg-success').removeClass('bg-danger');
        $('#importErrorLog').hide();
        $('#importErrorList').empty();

        $.ajax({
            url: "{{ route('software.import') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if(response.status === 'processing') {
                    $('#importStatusText').text(response.message);
                    toastr.info('Proses import dimulai di background');
                    $('#btnProsesImport').hide();
                    $('#btnReloadImport').show(); // Fallback button
                }
            },
            error: function(xhr) {
                $('#btnProsesImport').prop('disabled', false).text('Proses Import');
                let errMsg = 'Gagal mengupload file';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                toastr.error(errMsg);
                $('#importStatusText').removeClass('text-primary').addClass('text-danger').text(errMsg);
                $('#importProgressBar').removeClass('bg-success bg-info bg-primary progress-bar-animated').addClass('bg-danger');
            }
        });
    });

    // Manual status check (fallback)
    window.checkImportStatus = function(cacheKey = null) {
        $('#btnReloadImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cek...');
        
        const url = cacheKey 
            ? "{{ route('software.import.status') }}?cache_key=" + cacheKey 
            : "{{ route('software.import.status') }}";

        $.get(url, function(data) {
            $('#btnReloadImport').prop('disabled', false).text('Cek Status Final');
            if (data.status === 'waiting' || !data.success_count) {
                toastr.info('Masih memproses, cek kembali dalam beberapa detik.');
            } else {
                // Manually trigger the completed logic
                toastr.success('Import telah selesai.');
                $('#importStatusText').removeClass('text-primary').addClass('text-success').text(data.message);
                $('#importProgressBar').css('width', '100%').text('100%').removeClass('progress-bar-animated');
                $('#btnReloadImport').hide();
                $('#btnProsesImport').hide();

                if (data.failed_count > 0 && data.errors) {
                    $('#importErrorLog').show();
                    const list = $('#importErrorList');
                    list.empty();
                    data.errors.forEach(err => {
                        list.append(`<li>${err}</li>`);
                    });
                } else {
                    setTimeout(() => window.location.reload(), 2000);
                }
            }
        });
    };
});
</script>
@stop
