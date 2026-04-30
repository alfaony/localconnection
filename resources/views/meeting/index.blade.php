@extends('adminlte::page')

@section('title', 'Daftar Rapat')

@section('content')
    @include('components.alert')
    @livewire('meeting-table')
@stop

@section('css')
    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
    <style>
        .gap-1 { gap: 0.25rem; }
        .gap-2 { gap: 0.5rem; }
        .btn-xs { padding: 0.2rem 0.45rem; font-size: .75rem; }
        .select2-container--bootstrap .select2-selection--multiple { min-height: 31px; }
    </style>
@stop

@section('js')
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
    (function () {
        'use strict';

        // ---------- helpers ----------
        function getWire() {
            var el = document.querySelector('#meeting-table-root, [wire\\:id]');
            if (!el) return null;
            var id = el.closest('[wire\\:id]') ? el.closest('[wire\\:id]').getAttribute('wire:id') : el.getAttribute('wire:id');
            return id ? window.livewire.find(id) : null;
        }

        // JS mirror – used to build the export URL
        window._meetingFilters = { search: '', meeting_type: '', date_start: '', date_end: '', user_ids: [] };

        // ---------- Date Range Picker ----------
        function initDateRange() {
            var $input = $('#meeting-date-range');
            if (!$input.length || typeof $.fn.daterangepicker === 'undefined') return;

            // destroy existing instance first
            if ($input.data('daterangepicker')) { $input.data('daterangepicker').remove(); }

            $input.daterangepicker({
                autoUpdateInput: false,
                locale: { format: 'DD-MM-YYYY', cancelLabel: 'Bersihkan', applyLabel: 'Terapkan' }
            });

            $input.on('apply.daterangepicker', function (ev, picker) {
                var start = picker.startDate.format('YYYY-MM-DD');
                var end   = picker.endDate.format('YYYY-MM-DD');
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' s/d ' + picker.endDate.format('DD-MM-YYYY'));
                window._meetingFilters.date_start = start;
                window._meetingFilters.date_end   = end;
                var w = getWire();
                if (w) { w.set('dateStart', start); w.set('dateEnd', end); }
            });

            $input.on('cancel.daterangepicker', function () {
                $(this).val('');
                window._meetingFilters.date_start = '';
                window._meetingFilters.date_end   = '';
                var w = getWire();
                if (w) { w.set('dateStart', ''); w.set('dateEnd', ''); }
            });
        }

        // ---------- Select2 User Filter ----------
        function initUserSelect2() {
            var $sel = $('#meeting-user-filter');
            if (!$sel.length || typeof $.fn.select2 === 'undefined') return;

            if ($sel.hasClass('select2-hidden-accessible')) { $sel.select2('destroy'); }

            $sel.select2({
                placeholder: '-- Pilih pengguna --',
                allowClear:  true,
                width:       '100%',
                theme:       'bootstrap'
            });

            // no pre-selection — default shows all
            window._meetingFilters.user_ids = [];

            $sel.off('change.mwire').on('change.mwire', function () {
                var ids = $(this).val() || [];
                window._meetingFilters.user_ids = ids;
                var w = getWire();
                if (w) { w.set('userIds', ids); }
            });
        }

        // ---------- SweetAlert ----------
        @if (session('success'))
            Swal.fire({ title: 'Berhasil!', text: '{{ session('success') }}', icon: 'success', confirmButtonText: 'OK' });
        @endif

        // ---------- Delete confirmation ----------
        function bindDeleteForms() {
            document.querySelectorAll('.delete-form').forEach(function (form) {
                if (form.dataset.bound) return;
                form.dataset.bound = '1';
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Yakin?', text: 'Data rapat akan dihapus permanen!', icon: 'warning',
                        showCancelButton: true, confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal', reverseButtons: true
                    }).then(function (r) { if (r.isConfirmed) form.submit(); });
                });
            });
        }

        // ---------- filtersReset event (emitted by Livewire resetFilters) ----------
        // Livewire v2: use window.livewire.on(), not window.addEventListener
        document.addEventListener('livewire:load', function () {
            window.livewire.on('filtersReset', function () {
                $('#meeting-date-range').val('');
                window._meetingFilters.date_start   = '';
                window._meetingFilters.date_end     = '';
                window._meetingFilters.search       = '';
                window._meetingFilters.meeting_type = '';
                $('#meeting-user-filter').val(null).trigger('change.select2');
                window._meetingFilters.user_ids = [];
            });
        });

        // ---------- Download button ----------
        $(document).on('click', '#btn-meeting-download', function () {
            window._meetingFilters.search       = $('#meeting-table-root input[wire\\:model\\.debounce\\.400ms]').val() || '';
            window._meetingFilters.meeting_type = $('#meeting-table-root select[wire\\:model\\.lazy]').val() || '';

            var params = new URLSearchParams();
            if (window._meetingFilters.search)       params.set('search',       window._meetingFilters.search);
            if (window._meetingFilters.meeting_type) params.set('meeting_type', window._meetingFilters.meeting_type);
            if (window._meetingFilters.date_start)   params.set('date_start',   window._meetingFilters.date_start);
            if (window._meetingFilters.date_end)     params.set('date_end',     window._meetingFilters.date_end);
            (window._meetingFilters.user_ids || []).forEach(function (id) { params.append('user_ids[]', id); });
            window.location.href = '{{ route("meeting.export") }}?' + params.toString();
        });

        // ---------- Mirror search/type changes for download ----------
        $(document).on('input', '#meeting-table-root input[wire\\:model\\.debounce\\.400ms]', function () {
            window._meetingFilters.search = $(this).val() || '';
        });
        $(document).on('change', '#meeting-table-root select[wire\\:model\\.lazy]', function () {
            window._meetingFilters.meeting_type = $(this).val() || '';
        });

        // ---------- Init on DOM ready ----------
        document.addEventListener('DOMContentLoaded', function () {
            initDateRange();
            initUserSelect2();
            bindDeleteForms();
        });

        // ---------- Re-init after Livewire re-renders ----------
        document.addEventListener('livewire:load', function () {
            Livewire.hook('message.processed', function () {
                // Select2 is inside wire:ignore, so DOM is preserved.
                // Only re-init if it was somehow destroyed.
                if ($('#meeting-user-filter').length && !$('#meeting-user-filter').hasClass('select2-hidden-accessible')) {
                    initUserSelect2();
                }
                bindDeleteForms();
            });
        });
    }());
    </script>
@stop
