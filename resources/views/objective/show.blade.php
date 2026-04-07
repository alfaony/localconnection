@extends('adminlte::page')

@section('title', 'Objective - ' . $objective->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $objective->name }}</h1>
        @canAccess('edit','objectives')
        <a href="{{ route('objective.edit', $objective->slug) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-edit mr-1"></i>Edit Objective
        </a>
        @endcanAccess
    </div>
@stop

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('objective.index') }}">Objective</a></li>
            <li class="breadcrumb-item active">{{ $objective->name }}</li>
        </ol>
    </nav>

    <div id="kr-alert"></div>

    {{-- Per-Division Cards --}}
    @forelse($objective->divisions as $division)
    @php $divKrs = $keyResultsByDivision->get($division->id, collect()); @endphp
    <div class="card mb-3" id="div-card-{{ $division->id }}">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h6 class="mb-0">
                <i class="fas fa-layer-group mr-2 text-primary"></i>{{ $division->name }}
                <span class="badge badge-primary badge-pill ml-2 kr-count-badge">{{ $divKrs->count() }} KR</span>
            </h6>
            @canAccess('store','objectives')
            <button class="btn btn-sm btn-success btn-create-kr ml-auto"
                data-division-id="{{ $division->id }}"
                data-division-name="{{ $division->name }}">
                <i class="fas fa-plus mr-1"></i>Tambah Key Result
            </button>
            @endcanAccess
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0" id="kr-table-{{ $division->id }}">
                <thead class="thead-light">
                    <tr>
                        <th>Key Result</th>
                        <th width="160">Tanggal</th>
                        <th width="100" class="text-center">Jumlah Tugas</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divKrs as $kr)
                    <tr id="kr-row-{{ $kr->id }}">
                        <td class="align-middle kr-result">{{ $kr->result }}</td>
                        <td class="align-middle kr-date small text-muted">{{ $kr->dateShow }}</td>
                        <td class="align-middle text-center">
                            <a href="{{ route('objective.showtask', $kr->slug) }}" class="badge badge-info">
                                {{ $kr->dailyTasks->count() }}
                            </a>
                        </td>
                        <td class="align-middle text-center">
                            @canAccess('update','objectives')
                            <button class="btn btn-xs btn-outline-primary btn-edit-kr mr-1"
                                data-id="{{ $kr->id }}"
                                data-result="{{ $kr->result }}"
                                data-start="{{ $kr->start_date }}"
                                data-end="{{ $kr->end_date }}"
                                data-division-id="{{ $kr->division_id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endcanAccess
                            @canAccess('destroy','objectives')
                            <button class="btn btn-xs btn-outline-danger btn-delete-kr" data-id="{{ $kr->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endcanAccess
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row-{{ $division->id }}">
                        <td colspan="4" class="text-center text-muted py-3 small">
                            Belum ada key result untuk divisi ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="alert alert-warning">
        Objective ini belum dijoin ke divisi manapun.
        <a href="{{ route('objective.edit', $objective->slug) }}">Tambah divisi</a>
    </div>
    @endforelse

    {{-- Legacy: Key Result tanpa Division --}}
    @php $legacyKrs = $keyResultsByDivision->get(null, collect()); @endphp
    @if($legacyKrs->isNotEmpty())
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0">
                <i class="fas fa-archive mr-2"></i>Key Result Tanpa Divisi (Legacy)
                <span class="badge badge-light badge-pill ml-2">{{ $legacyKrs->count() }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Key Result</th>
                        <th width="160">Tanggal</th>
                        <th width="100" class="text-center">Jumlah Tugas</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($legacyKrs as $kr)
                    <tr id="kr-row-{{ $kr->id }}">
                        <td class="align-middle kr-result">{{ $kr->result }}</td>
                        <td class="align-middle kr-date small text-muted">{{ $kr->dateShow }}</td>
                        <td class="align-middle text-center">
                            <a href="{{ route('objective.showtask', $kr->slug) }}" class="badge badge-info">
                                {{ $kr->dailyTasks->count() }}
                            </a>
                        </td>
                        <td class="align-middle text-center">
                            @canAccess('update','objectives')
                            <button class="btn btn-xs btn-outline-primary btn-edit-kr mr-1"
                                data-id="{{ $kr->id }}"
                                data-result="{{ $kr->result }}"
                                data-start="{{ $kr->start_date }}"
                                data-end="{{ $kr->end_date }}"
                                data-division-id="{{ $kr->division_id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endcanAccess
                            @canAccess('destroy','objectives')
                            <button class="btn btn-xs btn-outline-danger btn-delete-kr" data-id="{{ $kr->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endcanAccess
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- ===== MODAL CREATE ===== --}}
<div class="modal fade" id="modalCreateKR" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Tambah Key Result — <span id="modalCreateDivName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="createDivisionId">
                <div id="kr-rows-container">
                    <div class="kr-row input-group mb-2">
                        <input type="text" class="form-control kr-result-input" placeholder="Key Result...">
                        <input type="date" class="form-control kr-start-input" style="max-width:150px;" title="Mulai">
                        <input type="date" class="form-control kr-end-input" style="max-width:150px;" title="Selesai">
                        <div class="input-group-append">
                            <button class="btn btn-outline-danger btn-remove-kr-row" type="button"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" id="btn-add-kr-row" class="btn btn-sm btn-outline-secondary mt-1">
                    <i class="fas fa-plus mr-1"></i>Baris Key Result
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" id="btn-save-kr" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL EDIT ===== --}}
<div class="modal fade" id="modalEditKR" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Key Result</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editKrId">
                <input type="hidden" id="editKrCurrentDivisionId">
                <div class="form-group" id="editDivisionWrapper">
                    <label>Pilih Divisi</label>
                    <select id="editKrDivision" class="form-control">
                        <option value="">-- Tanpa Divisi (Legacy) --</option>
                        @foreach($objective->divisions as $div)
                        <option value="{{ $div->id }}" data-name="{{ $div->name }}">{{ $div->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Key Result <span class="text-danger">*</span></label>
                    <input type="text" id="editKrResult" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="col">
                        <label>Mulai Tanggal</label>
                        <input type="date" id="editKrStart" class="form-control">
                    </div>
                    <div class="col">
                        <label>Selesai Tanggal</label>
                        <input type="date" id="editKrEnd" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" id="btn-update-kr" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const STORE_URL  = '{{ route("objective.key-result.store", $objective->id) }}';
    const UPDATE_BASE = '{{ url("objective/key-result") }}';
    const DELETE_BASE = '{{ url("objective/key-result") }}';
    const CSRF = '{{ csrf_token() }}';

    function showAlert(type, msg) {
        $('#kr-alert').html(`<div class="alert alert-${type} alert-dismissible fade show">
            ${msg}<button type="button" class="close" data-dismiss="alert">&times;</button></div>`);
        setTimeout(() => $('#kr-alert .alert').alert('close'), 4000);
    }

    function setLoading($btn, loading, defaultHtml) {
        $btn.prop('disabled', loading)
            .html(loading ? '<span class="spinner-border spinner-border-sm"></span> Menyimpan...' : defaultHtml);
    }

    function newKrRow() {
        return `<div class="kr-row input-group mb-2">
            <input type="text" class="form-control kr-result-input" placeholder="Key Result...">
            <input type="date" class="form-control kr-start-input" style="max-width:150px;" title="Mulai">
            <input type="date" class="form-control kr-end-input" style="max-width:150px;" title="Selesai">
            <div class="input-group-append">
                <button class="btn btn-outline-danger btn-remove-kr-row" type="button"><i class="fas fa-times"></i></button>
            </div>
        </div>`;
    }

    // Open create modal
    $(document).on('click', '.btn-create-kr', function () {
        $('#createDivisionId').val($(this).data('division-id'));
        $('#modalCreateDivName').text($(this).data('division-name'));
        $('#kr-rows-container').html(newKrRow());
        $('#modalCreateKR').modal('show');
    });

    $('#btn-add-kr-row').on('click', () => $('#kr-rows-container').append(newKrRow()));

    $(document).on('click', '.btn-remove-kr-row', function () {
        if ($('#kr-rows-container .kr-row').length > 1) $(this).closest('.kr-row').remove();
    });

    // Save create
    $('#btn-save-kr').on('click', function () {
        const $btn = $(this);
        const divisionId = $('#createDivisionId').val();
        const keyResults = [];
        let valid = true;

        $('#kr-rows-container .kr-row').each(function () {
            const result = $(this).find('.kr-result-input').val().trim();
            if (!result) { valid = false; return false; }
            keyResults.push({
                result,
                start_date: $(this).find('.kr-start-input').val() || null,
                end_date:   $(this).find('.kr-end-input').val()   || null,
            });
        });

        if (!valid || !keyResults.length) { showAlert('warning', 'Isi minimal 1 key result.'); return; }

        setLoading($btn, true, '<i class="fas fa-save mr-1"></i>Simpan');
        $.ajax({
            url: STORE_URL, method: 'POST',
            data: JSON.stringify({ division_id: divisionId || null, key_results: keyResults, _token: CSRF }),
            contentType: 'application/json',
            success(res) {
                if (!res.success) { showAlert('danger', 'Gagal menyimpan.'); return; }

                const $tbody = $(`#kr-table-${divisionId} tbody`);
                $tbody.find(`.empty-row-${divisionId}`).remove();

                res.data.forEach(kr => {
                    $tbody.append(`<tr id="kr-row-${kr.id}">
                        <td class="align-middle kr-result">${kr.result}</td>
                        <td class="align-middle kr-date small text-muted">${kr.date_show}</td>
                        <td class="align-middle text-center"><span class="badge badge-info">0</span></td>
                        <td class="align-middle text-center">
                            <button class="btn btn-xs btn-outline-primary btn-edit-kr mr-1"
                                data-id="${kr.id}" data-result="${kr.result}" data-start="" data-end="">
                                <i class="fas fa-edit"></i></button>
                            <button class="btn btn-xs btn-outline-danger btn-delete-kr" data-id="${kr.id}">
                                <i class="fas fa-trash"></i></button>
                        </td></tr>`);
                });

                const $badge = $(`#div-card-${divisionId} .kr-count-badge`);
                const cur = parseInt($badge.text()) || 0;
                $badge.text((cur + res.data.length) + ' KR');

                $('#modalCreateKR').modal('hide');
                showAlert('success', `${res.data.length} key result berhasil ditambahkan.`);
            },
            error() { showAlert('danger', 'Terjadi kesalahan server.'); },
            complete() { setLoading($btn, false, '<i class="fas fa-save mr-1"></i>Simpan'); }
        });
    });

    // Open edit modal
    $(document).on('click', '.btn-edit-kr', function () {
        const divisionId = $(this).data('division-id') || '';
        $('#editKrId').val($(this).data('id'));
        $('#editKrResult').val($(this).data('result'));
        $('#editKrStart').val($(this).data('start') || '');
        $('#editKrEnd').val($(this).data('end') || '');
        $('#editKrCurrentDivisionId').val(divisionId);

        // Pre-select division
        $('#editKrDivision').val(divisionId);

        $('#modalEditKR').modal('show');
    });

    // Save edit
    $('#btn-update-kr').on('click', function () {
        const $btn = $(this);
        const id = $('#editKrId').val();
        const result = $('#editKrResult').val().trim();
        const currentDivisionId = $('#editKrCurrentDivisionId').val();
        const selectedDivisionId = $('#editKrDivision').val();

        if (!result) { showAlert('warning', 'Key result tidak boleh kosong.'); return; }

        setLoading($btn, true, '<i class="fas fa-save mr-1"></i>Update');
        $.ajax({
            url: `${UPDATE_BASE}/${id}`, method: 'POST',
            data: {
                _method: 'PUT', _token: CSRF, result,
                start_date: $('#editKrStart').val(),
                end_date:   $('#editKrEnd').val(),
                division_id: selectedDivisionId || '',
            },
            success(res) {
                if (!res.success) { showAlert('danger', 'Gagal mengupdate.'); return; }

                // Jika pindah divisi, reload page
                if (currentDivisionId != (res.data.division_id || '')) {
                    window.location.reload();
                    return;
                }

                const $row = $(`#kr-row-${id}`);
                // Update row in place
                $row.find('.kr-result').text(res.data.result);
                $row.find('.kr-date').text(res.data.date_show);
                $row.find('.btn-edit-kr').data('result', res.data.result);

                $('#modalEditKR').modal('hide');
                showAlert('success', 'Key result berhasil diupdate.');
            },
            error() { showAlert('danger', 'Terjadi kesalahan server.'); },
            complete() { setLoading($btn, false, '<i class="fas fa-save mr-1"></i>Update'); }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete-kr', function () {
        if (!confirm('Hapus key result ini?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: `${DELETE_BASE}/${id}`, method: 'POST',
            data: { _method: 'DELETE', _token: CSRF },
            success(res) {
                if (!res.success) { showAlert('danger', 'Gagal menghapus.'); return; }
                $(`#kr-row-${id}`).remove();
                showAlert('success', 'Key result dihapus.');
            },
            error() { showAlert('danger', 'Terjadi kesalahan server.'); }
        });
    });
})();
</script>
@endsection

@section('css')
<style>
    .btn-xs { padding: .15rem .4rem; font-size: .75rem; }
    #kr-alert { position: sticky; top: 10px; z-index: 1050; }
</style>
@endsection
