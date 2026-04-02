<script>
(function() {
    @if(isset($primaryDivision) && $primaryDivision)
    var PRIMARY_DIVISION_ID = '{{ $primaryDivision->id }}';
    @else
    var PRIMARY_DIVISION_ID = null;
    @endif

    @if(isset($userDivisions) && $userDivisions->isNotEmpty())
    var USER_DIVISION_IDS = @json($userDivisions->pluck('id'));
    @else
    var USER_DIVISION_IDS = [];
    @endif

    // Hapus option yang tidak relevan SEBELUM select2 diinisialisasi
    // (script ini berjalan sinkron, sebelum $().select2() generik dipanggil)
    $('.objective-select').each(function() {
        $(this).find('option').each(function() {
            var $opt = $(this);
            if (!$opt.val()) return;
            var divId = $opt.data('division-id') || '';

            var keep = false;
            if (PRIMARY_DIVISION_ID) {
                // Jika ada division induk: hanya tampilkan objective dari division induk
                keep = (divId === PRIMARY_DIVISION_ID);
            } else if (USER_DIVISION_IDS.length > 0) {
                // Jika tidak ada division induk: tampilkan objective dari semua division yang diikuti user
                keep = USER_DIVISION_IDS.indexOf(divId) !== -1;
            } else {
                keep = true;
            }

            if (!keep) $opt.remove();
        });
    });

    // Ketika user memilih objective, simpan data-division-id ke hidden input
    $(document).on('select2:select', '.objective-select', function(e) {
        var divisionId = $(e.params.data.element).data('division-id') || '';
        var $container = $(this).closest('.col-md-6, .form-group, .card-body');
        $container.find('.objective-division-id-input').val(divisionId);
    });

    // Fallback native change
    $(document).on('change', '.objective-select', function() {
        if ($(this).hasClass('select2-hidden-accessible')) return;
        var divisionId = $(this).find('option:selected').data('division-id') || '';
        var $container = $(this).closest('.col-md-6, .form-group, .card-body');
        $container.find('.objective-division-id-input').val(divisionId);
    });
})();
</script>
