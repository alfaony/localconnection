@extends('adminlte::page')

@section('content_header')
    <h2>Buat Main Proyek</h2>
@stop

@section('content')
<div class="card p-3">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card-body">
        <form action="{{ route('daily_task_project.update', $dailyTaskProject->slug) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nama Proyek</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $dailyTaskProject->name }}" required>
            </div>
            <div class="form-group">
                <label for="projects">Proyek Terkait</label>
                <select class="form-control select2" id="projects" name="projects[]" multiple="multiple" required>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ $dailyTaskProject->projects->contains($project->id) ? 'selected' : '' }}>
                            {{ $project->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-submit"></i> Perubahan</button>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select Projects',
            allowClear: true
        });
    });
</script>
<script>
    $(document).ready(function() {
        let customFieldIndex = 0;

        // <i class="fa fa-plus"></i>  Custom Field
        $('#add-custom-field').click(function() {
            customFieldIndex++;
            const customFieldTemplate = `
            <div class="custom-field card mb-3">
                <div class="card-body">
                <div class="form-row">
                    <div class="col-md-8 mb-3">
                    <label for="custom_field_name">Nama Custom Field</label>
                    <input type="text" class="form-control custom-field-name" name="custom_field_name[]" required>
                    </div>
                    <div class="col-md-4 mb-3">
                    <label for="custom_field_type">Tipe</label>
                    <select class="form-control custom-field-type" name="custom_field_type[]" required>
                        @foreach ($statusSelect as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="custom-field-values">
                    <div class="form-group d-flex">
                    <input type="text" class="form-control custom-field-value" name="custom_field_value[${customFieldIndex}][]" required>
                    <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i> </button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary add-custom-field-value"><i class="fa fa-plus"></i>  Option</button>
                <button type="button" class="btn btn-danger remove-custom-field "><i class="fa fa-trash"></i> Custom Field</button>
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
                    <input type="text" class="form-control custom-field-value" name="custom_field_value[${index}][]" required>
                    <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i> </button>
                </div>
            `;
            $(this).siblings('.custom-field-values').append(customFieldValueTemplate);
        });

        // <i class="fa fa-trash"></i> Custom Field
        $(document).on('click', '.remove-custom-field', function() {
            $(this).closest('.custom-field').remove();
        });

        // <i class="fa fa-trash"></i> Option
        $(document).on('click', '.remove-custom-field-value', function() {
            $(this).closest('.form-group').remove();
        });
    });
</script>
@endsection
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
.select2-selection__choice
    {
        background-color: #007bff !important;
        border: 1px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 1px solid #007bff !important;
    }
</style>
@endsection
