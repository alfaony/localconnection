@extends('adminlte::page')

@section('title', 'Daftar Rapat')

@section('content')
    @include('components.alert')
    @livewire('mom-table')
@stop

@section('js')
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
    /* ════════════════════════════════════════════════════════════════
       CORE HELPER
       Mengupdate hidden input (wire:model) dan memicu event 'input'
       sehingga Livewire mendeteksi perubahan — tanpa Livewire.emit
    ════════════════════════════════════════════════════════════════ */
    function setWireInput(id, val) {
        var el = document.getElementById(id);
        if (!el) return;
        el.value = (val === null || val === undefined) ? '' : val;
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /* ════════════════════════════════════════════════════════════════
       SELECT2
       Re-init setiap livewire:update karena Livewire morphdom
       menghapus Select2 yang terpasang di elemen.
       Selected value dibaca dari atribut 'selected' hasil render Blade.
    ════════════════════════════════════════════════════════════════ */
    function initMomSelect2() {
        // -- Project --
        initOneSelect('#mom-project-select', 'Semua Project', function (val) {
            setWireInput('h-mom-project', val);
            // meetingId direset oleh updatingProjectId() di PHP
        });

        // -- Meeting --
        initOneSelect('#mom-meeting-select', 'Semua Meeting', function (val) {
            setWireInput('h-mom-meeting', val);
        });

        // -- User --
        initOneSelect('#mom-user-select', 'Semua Anggota', function (val) {
            setWireInput('h-mom-user', val);
        });
    }

    function initOneSelect(selector, placeholder, onChangeFn) {
        var $el = $(selector);
        if (!$el.length) return;

        // Hancurkan instance lama agar tidak double-bind
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }

        $el.select2({
            placeholder:    placeholder,
            allowClear:     true,
            width:          '100%',
            dropdownParent: $(document.body),
            language: {
                noResults: function () { return 'Data tidak ditemukan'; },
                searching: function () { return 'Mencari...'; },
            },
        }).on('change', function () {
            onChangeFn($(this).val() || '');
        });
    }

    // Init pertama kali saat DOM siap
    document.addEventListener('DOMContentLoaded', initMomSelect2);

    // Re-init setiap Livewire re-render (morphdom bisa hapus Select2)
    document.addEventListener('livewire:update', initMomSelect2);

    // Reset visual Select2 saat tombol Reset diklik (filtersReset di-emit PHP)
    document.addEventListener('livewire:load', function () {
        Livewire.on('filtersReset', function (dateFrom, dateTo, userId) {
            // Reset Select2 secara visual — nilai sudah di-reset di PHP
            $('#mom-project-select').val('').trigger('change.select2');
            $('#mom-meeting-select').val('').trigger('change.select2');
            $('#mom-user-select').val(userId || '').trigger('change.select2');

            // Reset daterangepicker visual
            var picker = $('#mom-daterange').data('daterangepicker');
            if (picker) {
                picker.setStartDate(moment(dateFrom, 'YYYY-MM-DD'));
                picker.setEndDate(moment(dateTo,     'YYYY-MM-DD'));
                $('#mom-daterange').val(
                    moment(dateFrom, 'YYYY-MM-DD').format('DD/MM/YYYY') + ' – ' +
                    moment(dateTo,   'YYYY-MM-DD').format('DD/MM/YYYY')
                );
            }
        });
    });

    /* ════════════════════════════════════════════════════════════════
       DATERANGEPICKER
       Komunikasi ke Livewire via setWireInput (hidden input),
       bukan Livewire.emit — lebih reliable di Livewire 2
    ════════════════════════════════════════════════════════════════ */
    function initMomDateRangePicker() {
        if (!$('#mom-daterange').length) return;

        var start = moment().startOf('isoWeek');
        var end   = moment().endOf('isoWeek');

        $('#mom-daterange').daterangepicker({
            startDate: start,
            endDate:   end,
            locale: {
                format:      'DD/MM/YYYY',
                applyLabel:  'Terapkan',
                cancelLabel: 'Batal',
                fromLabel:   'Dari',
                toLabel:     'Ke',
                daysOfWeek:  ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
                monthNames:  ['Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
                firstDay: 1,
            },
        }, function (s, e) {
            // Kirim ke Livewire via hidden input — tidak butuh Livewire.emit
            setWireInput('h-mom-datefrom', s.format('YYYY-MM-DD'));
            setWireInput('h-mom-dateto',   e.format('YYYY-MM-DD'));
            $('#mom-daterange').val(s.format('DD/MM/YYYY') + ' – ' + e.format('DD/MM/YYYY'));
        });

        // Tampilkan value default
        $('#mom-daterange').val(start.format('DD/MM/YYYY') + ' – ' + end.format('DD/MM/YYYY'));
    }

    document.addEventListener('DOMContentLoaded', initMomDateRangePicker);

    /* ════════════════════════════════════════════════════════════════
       TOOLTIP & DELETE CONFIRM
    ════════════════════════════════════════════════════════════════ */
    function initTooltips() {
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .forEach(function (el) { bootstrap.Tooltip.getOrCreateInstance(el); });
    }

    function bindDeleteForms() {
        document.querySelectorAll('.delete-form').forEach(function (form) {
            if (form.dataset.bound) return;
            form.dataset.bound = '1';
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin menghapus MoM ini?')) this.submit();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTooltips();
        bindDeleteForms();
    });
    document.addEventListener('livewire:update', function () {
        initTooltips();
        bindDeleteForms();
    });

    /* ── SweetAlert ── */
    @if (session('success'))
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Berhasil!',
                text:  '{{ session('success') }}',
                icon:  'success',
                confirmButtonText: 'OK',
            });
        });
    @endif
    </script>
@stop

@section('css')
    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        /* ── Filter labels ── */
        .filter-label {
            display: block;
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        /* ── Sort buttons ── */
        .btn-sort {
            font-size: 0.75rem;
            padding: 0.2rem 0.65rem;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            background: #fff;
            color: #495057;
            transition: background 0.15s, border-color 0.15s;
            white-space: nowrap;
            line-height: 1.5;
        }
        .btn-sort:hover  { background: #f1f3f5; border-color: #adb5bd; }
        .btn-sort.active { background: #0d6efd; border-color: #0d6efd; color: #fff; }

        /* ── MoM card ── */
        .mom-card {
            border-radius: 10px;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .mom-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.09) !important;
        }

        /* ── Badges ── */
        .badge-project,
        .badge-meeting,
        .badge-user {
            font-size: 0.72rem;
            font-weight: 500;
            padding: 0.28rem 0.55rem;
            border-radius: 20px;
        }
        .badge-project { background-color: #dbeafe; color: #1d4ed8; }
        .badge-meeting  { background-color: #dcfce7; color: #15803d; }
        .badge-user     { background-color: #fef9c3; color: #854d0e; }

        /* ── Notes preview ── */
        .notes-preview { font-size: 0.875rem; line-height: 1.5; margin-bottom: 0; }

        /* ── Action buttons ── */
        .mom-action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        /* ── Select2 — sesuaikan tinggi form-select-sm (31px) ── */
        .select2-container--default .select2-selection--single {
            height: 31px !important;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px !important;
            padding-left: 8px;
            font-size: 0.875rem;
            color: #212529;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 29px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
        }
        .select2-container--default .select2-results__option { font-size: 0.875rem; padding: 5px 10px; }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            font-size: 0.875rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-top: 3px;
            font-size: 1rem;
            font-weight: bold;
            color: #6c757d;
        }

        /* ── Daterangepicker ── */
        .daterangepicker .calendar-table th,
        .daterangepicker .calendar-table td { font-size: 0.8rem; }

        /* ── Mobile ── */
        @media (max-width: 576px) {
            .mom-card .d-flex.justify-content-between { flex-direction: column; gap: 0.5rem; }
        }
    </style>
@stop
