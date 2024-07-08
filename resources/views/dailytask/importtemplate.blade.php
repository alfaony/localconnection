@extends('adminlte::page')

@section('content')

<div class="card mt-3">
    <div class="card-header">
        <h5>Import Tugas Harian</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('dailytask.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Select Objective and Main Project -->
            <div id="dynamic-form-fields">
                <div class="dynamic-field row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="assignment_user_id">Objective</label>
                            @canAccess('getresult','objectives')
                            <select name="objective_id" class="form-control objective-select select2" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                @foreach($objectives as $objective)
                                    <option value="{{ $objective->id }}">{{ ucfirst($objective->name) }}</option>
                                @endforeach
                            </select>
                            @endcanAccess
                        </div>
                        <div id="keyresult-fields-container-0"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="project_id">Pilih Main Proyek</label>
                            @canAccess('getcustomfield','daily_task_projects')
                            <select class="form-control select2 project-select" name="project_id" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ ucfirst($project->name) }}</option>
                                @endforeach
                            </select>
                            @endcanAccess
                        </div>
                        <div id="custom-fields-container-0"></div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Download Template -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="row col-md-12">
                        <label for="file">Download Template</label>
                    </div>
                    @canAccess('downloadtemplate','dailytasks')
                    <div class="row col-md-12">
                        <a href="{{ route('dailytask.downloadtemplate') }}" class="btn btn-info">
                            <i class="fa fa-download"></i> Download Template
                        </a>
                    </div>
                    @endcanAccess
                </div>
            </div>

            <!-- Step 3: Upload CSV File -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="file">Upload CSV File</label>
                        <input type="file" name="import_file" class="form-control" required>
                    </div>
                    @canAccess('import','dailytasks')
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload"></i> Import
                    </button>
                    @endcanAccess
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>

<script>
    $(document).ready(function() 
    {
        // initializeSelect2();
        $('.select2').select2({
            placeholder: 'Pilih',
            allowClear: true
        });
        $('.category-select2').select2();

        $('#dynamic-form-fields').on('change', '.project-select', function() {
            var projectId = $(this).val();
            var index = $(this).closest('.dynamic-field').index();
            console.log(index);

            if (projectId) {
                $.ajax({
                    url: '{{ url('daily_task_project/getcustomfield') }}/' + projectId,
                    data:
                    {
                        index:index
                    },
                    type: 'GET',
                    success: function(data) 
                    {
                        $('#custom-fields-container-' + index).html(data);
                        initializeSelect2ForContainer(index);
                    }
                });
            } else {
                $('#custom-fields-container-' + index).html('');
            }
        });


        $('#dynamic-form-fields').on('change', '.objective-select', function() {
            var objective = $(this).val();
            var index = $(this).closest('.dynamic-field').index();
            console.log(index);

            if (objective) {
                $.ajax({
                    url: '{{ url('objective/getresult') }}/' + objective,
                    data:
                    {
                        index:index
                    },
                    type: 'GET',
                    success: function(data) 
                    {
                        $('#keyresult-fields-container-' + index).html(data);
                        initializeSelect2ForContainer(index);
                    }
                });
            } else {
                $('#keyresult-fields-container-' + index).html('');
            }
        });



        });

        function initializeSelect2() 
        {
            $('.select3').select2({
                placeholder: 'Pilih',
            });
            $('.category-select3').select2();
        }

        function initializeSelect2ForContainer(index) 
        {
            $('.select2-single-'+index+', .select2-multiple-'+index+'').select2({
                width: '100%' // Adjust width as needed
            });
        }

        function generateRandomString(length) 
        {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }

</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .container {
        background-color: #fff;
        border-radius: 5px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    .card {
        margin-bottom: 20px;
    }
    .card-body {
        padding: 20px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .input-group-text {
        padding: 0 10px;
    }
    .remove-button {
        padding: 0 10px;
    }
    .add-button {
        margin-bottom: 20px;
    }
    .thriveEditor {
        height: 100px;
    }
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