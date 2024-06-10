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
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('objective.update',$objective->slug) }}">
        @csrf
        @method('PUT')
        <div id="custom-fields-container">
            <div class="custom-field card mb-3">
                <div class="card-body">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label for="custom_field_name">Misi</label>
                            <select class="form-control custom-field-type" name="mission_id" required>
                                <option selected disabled>-- Pilih Misi --</option>
                                @foreach ($missions as $mission)
                                    <option value="{{ $mission->id }}" {{ $objective->mission_id == $mission->id ? 'selected' : '' }}>{{ $mission->mission }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="custom_field_name">Objective</label>
                            <input type="text" class="form-control custom-field-name" name="objective_name" value="{{ $objective->name }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="custom_field_type">Divisi</label>
                            <select class="form-control custom-field-type" name="division_id" required>
                                <option disabled selected>--Pilih--</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" {{ $objective->division_id == $division->id ? 'selected' : '' }} >{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="assignment_user_id">Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control start-date" name="start_date_objective" placeholder="Mulai Tanggal" value="{{ $objective->start_date }}" >
                                    <span class="input-group-text">hingga</span>
                                    <input type="date" class="form-control end-date" name="end_date_objective" placeholder="Sampai Tanggal" value="{{ $objective->end_date }}" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="custom-field-values">
                        <label for="custom_field_type">Key Result</label>
                        @if($objective->keyResults)
                        @foreach ($objective->keyResults as $keyResult)
                        <div class="form-group d-flex">
                            <input type="hidden" class="form-control custom-field-value" name="key_result_id[]" value="{{ $keyResult->id }}" required>
                            <input type="text" class="form-control custom-field-value" name="key_result[]" value="{{ $keyResult->result }}" required>
                            <input type="date" class="form-control start-date" name="start_date[]" placeholder="Mulai Tanggal" value="{{ $keyResult->start_date }}">
                            <input type="date" class="form-control end-date" name="end_date[]" placeholder="Sampai Tanggal" value="{{ $keyResult->end_date }}">
                            <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i> </button>
                        </div>
                        @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary add-custom-field-value"><i class="fa fa-plus"></i> Key Result</button>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Objective</button>
    </form>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let customFieldIndex = 0;

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
                    <input type="hidden" class="form-control custom-field-value" name="key_result[${customFieldIndex}][]" value="" required>
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
            syncDates();
        });

        $(document).on('click', '.add-custom-field-value', function() {
            const index = $(this).closest('.custom-field').index();
            const customFieldValueTemplate = `
                <div class="form-group d-flex">
                    <input type="hidden" class="form-control custom-field-value" name="key_result_id[]" value="" required>
                    <input type="text" class="form-control custom-field-value" name="key_result[]" value="" required>
                    <input type="date" class="form-control start-date" name="start_date[]" placeholder="Mulai Tanggal">
                    <input type="date" class="form-control end-date" name="end_date[]" placeholder="Sampai Tanggal">
                    <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i> </button>
                </div>
            `;
            $(this).siblings('.custom-field-values').append(customFieldValueTemplate);
            syncDates();
        });

        $(document).on('click', '.remove-custom-field', function() {
            $(this).closest('.custom-field').remove();
        });

        $(document).on('click', '.remove-custom-field-value', function() {
            $(this).closest('.form-group').remove();
        });

        function syncDates() {
            $('.start-date').change(function() {
                let startDateValue = $(this).val();
                $(this).closest('.input-group').find('.end-date').val(startDateValue);
            });

            $('.start-date').change(function() {
                let startDateValue = $(this).val();
                $(this).closest('.form-group').find('.end-date').val(startDateValue);
            });
        }

        syncDates(); // Initial call to setup the listeners
    });
</script>
@endsection