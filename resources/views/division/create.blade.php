@extends('adminlte::page')

@section('content_header')
    <h2>Objective</h2>
@stop

@section('content')
<div class="container p-3">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <form method="POST" action="{{ route('objective.store') }}">
        @csrf
        <div id="custom-fields-container">
            <div class="custom-field card mb-3">
                <div class="card-body">
                    <div class="form-row">
                        <div class="col-md-8 mb-3">
                            <label for="custom_field_name">Objective</label>
                            <input type="text" class="form-control custom-field-name" name="objective_name[]" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="custom_field_type">Divisi</label>
                            <select class="form-control custom-field-type" name="division_id[]" required>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="assignment_user_id">Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control start-date" name="start_date_objective[]" placeholder="Mulai Tanggal">
                                    <span class="input-group-text">hingga</span>
                                    <input type="date" class="form-control end-date" name="end_date_objective[]" placeholder="Sampai Tanggal">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="custom-field-values">
                        <label for="custom_field_type">Key Result</label>
                        <div class="form-group d-flex">
                            <input type="text" class="form-control custom-field-value" name="key_result[0][]" required>
                            <input type="date" class="form-control start-date" name="start_date[0][]" placeholder="Mulai Tanggal">
                            <input type="date" class="form-control end-date" name="end_date[0][]" placeholder="Sampai Tanggal">
                            <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i> </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary add-custom-field-value"><i class="fa fa-plus"></i> Key Result</button>
                    <button type="button" class="btn btn-danger remove-custom-field"><i class="fa fa-trash"></i> Objective</button>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary" id="add-custom-field"><i class="fa fa-plus"></i>  Objective</button>
        <button type="submit" class="btn btn-primary">Simpan Objective</button>
    </form>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let customFieldIndex = 0;

        // <i class="fa fa-plus"></i>  Objective
        $('#add-custom-field').click(function() {
            customFieldIndex++;
            const customFieldTemplate = `
            <div class="custom-field card mb-3">
                <div class="card-body">
                <div class="form-row">
                    <div class="col-md-8 mb-3">
                    <label for="custom_field_name">Nama Objective</label>
                    <input type="text" class="form-control custom-field-name" name="objective_name[]" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="custom_field_type">Divisi</label>
                        <select class="form-control custom-field-type" name="division_id[]" required>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="assignment_user_id">Tanggal</label>
                            <div class="input-group">
                                <input type="date" class="form-control start-date" name="start_date_objective[]" placeholder="Mulai Tanggal">
                                <span class="input-group-text">hingga</span>
                                <input type="date" class="form-control end-date" name="end_date_objective[]" placeholder="Sampai Tanggal">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="custom-field-values">
                    <label for="custom_field_type">Key Result</label>
                    <div class="form-group d-flex">
                    <input type="text" class="form-control custom-field-value" name="key_result[${customFieldIndex}][]" required>
                    <input type="date" class="form-control start-date" name="start_date[${customFieldIndex}][]" placeholder="Mulai Tanggal">
                    <input type="date" class="form-control end-date" name="end_date[${customFieldIndex}][]" placeholder="Sampai Tanggal">
                    <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i> </button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary add-custom-field-value"><i class="fa fa-plus"></i>  Key Result</button>
                <button type="button" class="btn btn-danger remove-custom-field "><i class="fa fa-trash"></i> Objective</button>
                </div>
            </div>
            `;
            $('#custom-fields-container').append(customFieldTemplate);
        });

        // <i class="fa fa-plus"></i>  Option
        $(document).on('click', '.add-custom-field-value', function() {
            const index = $(this).closest('.custom-field').index();
            const customFieldValueTemplate = `
                <div class="form-group d-flex">
                    <input type="text" class="form-control custom-field-value" name="key_result[${index}][]" required>
                    <input type="date" class="form-control start-date" name="start_date[${index}][]" placeholder="Mulai Tanggal">
                    <input type="date" class="form-control end-date" name="end_date[${index}][]" placeholder="Sampai Tanggal">
                    <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i> </button>
                </div>
            `;
            $(this).siblings('.custom-field-values').append(customFieldValueTemplate);
        });

        // <i class="fa fa-trash"></i> Objective
        $(document).on('click', '.remove-custom-field', function() {
            $(this).closest('.custom-field').remove();
        });

        // <i class="fa fa-trash"></i> Option
        $(document).on('click', '.remove-custom-field-value', function() {
            $(this).closest('.form-group').remove();
        });

        $(document).ready(function () {
            $('#custom-fields-container').on('change', '.start-date', function() {
                var startDateValue = $(this).val();
                $(this).closest('.dynamic-field').find('.end-date').val(startDateValue);
            });

            $('input[name="start_date"]').on('change', function() {
                var startDateValue = $(this).val();
                $('input[name="end_date"]').val(startDateValue);
            });
        });
    });
</script>
@endsection