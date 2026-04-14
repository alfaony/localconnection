{{-- Grouped User Select – JS init + division pill logic --}}
{{-- Include ONCE per page in @section('js'), after jQuery & Select2 are loaded --}}
{{-- Pass $userSelectIds as an array of select IDs to initialise, e.g. ['users-select', 'invite-select'] --}}
<script>
(function () {
    var selectIds = {!! json_encode($userSelectIds ?? []) !!};

    selectIds.forEach(function (id) {
        var $sel = $('#' + id);
        if (!$sel.length) return;

        // ── Init Select2 ──────────────────────────────────────
        $sel.select2({
            placeholder: $sel.data('placeholder') || 'Pilih karyawan...',
            allowClear : true,
            width      : '100%',
            templateResult: function (data) {
                // Indent option slightly inside group
                if (!data.id) return data.text;
                return $('<span style="padding-left:4px;">').text(data.text);
            },
        });

        // ── Update "X dipilih" counter ────────────────────────
        function updateCounter() {
            var n = $sel.val() ? $sel.val().length : 0;
            $('#' + id + '-count').text(n + ' dipilih');

            // Sync pill active state
            $('[data-select-id="' + id + '"].division-pill').each(function () {
                var div = $(this).data('division');
                var ids = getIdsForDivision(id, div);
                var cur = $sel.val() || [];
                var allOn = ids.length > 0 && ids.every(function (v) { return cur.indexOf(v) > -1; });
                $(this).toggleClass('active', allOn);
            });
        }
        $sel.on('change', updateCounter);
        updateCounter();
    });

    // ── Division pill click handler ────────────────────────────
    $(document).on('click', '.division-pill', function () {
        var selId = $(this).data('select-id');
        var div   = $(this).data('division');
        var $sel  = $('#' + selId);
        var ids   = getIdsForDivision(selId, div);
        var cur   = $sel.val() || [];
        var allOn = ids.length > 0 && ids.every(function (v) { return cur.indexOf(v) > -1; });

        var newVals;
        if (allOn) {
            // Deselect all in this division
            newVals = cur.filter(function (v) { return ids.indexOf(v) === -1; });
        } else {
            // Select all in this division (merge)
            newVals = cur.slice();
            ids.forEach(function (v) { if (newVals.indexOf(v) === -1) newVals.push(v); });
        }
        $sel.val(newVals).trigger('change');
    });

    // ── Select-all pill ────────────────────────────────────────
    $(document).on('click', '.select-all-pill', function () {
        var selId = $(this).data('select-id');
        var $sel  = $('#' + selId);
        var all   = $sel.find('option').map(function () { return $(this).val(); }).get();
        $sel.val(all).trigger('change');
    });

    // ── Clear-all pill ─────────────────────────────────────────
    $(document).on('click', '.clear-all-pill', function () {
        var selId = $(this).data('select-id');
        $('#' + selId).val([]).trigger('change');
    });

    // ── Helper: collect option IDs for a division ──────────────
    function getIdsForDivision(selId, divName) {
        var $sel = $('#' + selId);
        var $opts;
        if (divName === '__no_division__') {
            $opts = $sel.find('optgroup[label="Tanpa Divisi"] option');
        } else {
            $opts = $sel.find('optgroup[label="' + divName + '"] option');
        }
        return $opts.map(function () { return $(this).val(); }).get();
    }
}());
</script>
