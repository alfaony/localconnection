<script>
(function() {
    @if(isset($primaryDivision) && $primaryDivision)
    var PRIMARY_DIVISION_ID = '{{ $primaryDivision->id }}';
    @else
    var PRIMARY_DIVISION_ID = null;
    @endif

    // Hapus option non-primary SEBELUM select2 diinisialisasi
    // (script ini berjalan sinkron, sebelum $().select2() generik dipanggil)
    if (PRIMARY_DIVISION_ID) {
        $('.objective-select').each(function() {
            $(this).find('option').each(function() {
                var $opt = $(this);
                if (!$opt.val()) return;
                var divId = $opt.data('division-id') || '';
                if (divId !== PRIMARY_DIVISION_ID) {
                    $opt.remove();
                }
            });
        });
    }

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
