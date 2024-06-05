@extends('adminlte::page')

@section('content')
<div class="container py-3">
    <div class="col-md-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <h2>Buat Tugas Harian</h2>
            <form action="{{ route('dailytask.store') }}" method="POST">
                @csrf

                <div id="dynamic-form-fields">
                    <div class="dynamic-field card mb-3">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="assignment_user_id">Objective</label>
                                @canAccess('getresult','objectives')
                                <select name="objective[]" class="form-control objective-select select2" required>
                                    <option selected disabled>Pilih Objective</option>
                                    @foreach($objectives as $objective)
                                        <option value="{{ $objective->id }}">{{ $objective->name }}</option>
                                    @endforeach
                                </select>
                                @endcanAccess
                            </div>
                            <div id="keyresult-fields-container-0"></div>
                            <div class="form-group">
                                <label for="assignment_user_id">Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control start-date" name="start_date[]" placeholder="Mulai Tanggal" required>
                                    <span class="input-group-text">hingga</span>
                                    <input type="date" class="form-control end-date" name="end_date[]" placeholder="Sampai Tanggal" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="assignment_user_id">Ditugaskan</label>
                                        <select name="assignment_user_id[]" class="form-control select2" required>
                                            <option selected disabled>Pilih Ditugaskan</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="category_id">Kategori</label>
                                        <select name="category_id[]" class="form-control select2 category-select2" required>
                                            <option selected disabled>Pilih Kategori</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="type_id">Jenis</label>
                                        <select name="type_id[]" class="form-control select2" required>
                                            <option selected disabled>Pilih Tipe</option>
                                            @foreach($types as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @canAccess('getcustomfield','daily_task_projects')
                                    <div class="form-group">
                                        <label for="project_id">Pilih Proyek</label>
                                        <select class="form-control select2 project-select" name="project_id[]" required>
                                            <option selected disabled>Pilih Proyek</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="custom-fields-container-0"></div>
                                    @endcanAccess
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Tugas</label>
                                        <input type="text" name="name[]" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Deskripsi</label>
                                        <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description[]" placeholder="yang akan dicetak di perjanjian"/>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-center">
                                    <button type="button" class="btn btn-danger remove-button mr-2 btn-sm"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-task-user" class="btn btn-info mb-2 add-button"><i class="fa fa-plus"></i> Tugas</button>
                <button type="submit" class="btn btn-success mb-2"><i class="fa fa-save"></i> Simpan</button>
            </form>
        </div>
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
        $('.select2').select2();
        $('.category-select2').select2({
            tags: true
        });

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

        $('.add-button').on('click', function() 
        {
            var indexKeys = generateRandomString(4);
            var newIndex = $('.dynamic-field').length;

            let fieldHTML = `
            <div class="dynamic-field card mb-3">
                    <div class="card-body">
                        <div class="form-group">
                                <label for="assignment_user_id">Objective</label>
                                <select name="objective[]" class="form-control objective-select select3" required>
                                    <option selected disabled>Pilih Objective</option>
                                    @foreach($objectives as $objective)
                                        <option value="{{ $objective->id }}">{{ $objective->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="keyresult-fields-container-${newIndex}"></div>
                        <div class="form-group">
                            <label for="assignment_user_id">Tanggal</label>
                            <div class="input-group">
                                <input type="date" class="form-control start-date" name="start_date[]" placeholder="Mulai Tanggal" required>
                                <span class="input-group-text">hingga</span>
                                <input type="date" class="form-control end-date" name="end_date[]" placeholder="Sampai Tanggal" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="assignment_user_id">Ditugaskan</label>
                                    <select name="assignment_user_id[]" class="form-control select3" required>
                                        <option selected disabled>Pilih Ditugaskan</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="category_id">Kategori</label>
                                    <select name="category_id[]" class="form-control select3 category-select3" required>
                                        <option selected disabled>Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="type_id">Jenis</label>
                                    <select name="type_id[]" class="form-control select3" required>
                                        <option selected disabled>Pilih Tipe</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="project_id">Pilih Proyek</label>
                                    <select class="form-control select3 project-select" name="project_id[]" required>
                                        <option selected disabled>Pilih Proyek</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="custom-fields-container-${newIndex}"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Tugas</label>
                                    <input type="text" name="name[]" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Deskripsi</label>
                                    <input class="thriveEditor form-control" id="description_description_${indexKeys}" data-ids="description_${indexKeys}" name="description[]" placeholder="yang akan dicetak di perjanjian"/>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-danger remove-button mr-2 btn-sm"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                `;
                $('#dynamic-form-fields').append(fieldHTML);

                initializeSelect2();
                generateThriveEditor("description_" + indexKeys);
                });

                $('#dynamic-form-fields').on('click', '.remove-button', function() {
                if ($('#dynamic-form-fields .dynamic-field').length > 1) {
                    $(this).closest('.dynamic-field').remove();
                }

            });

            $('#dynamic-form-fields').on('change', '.start-date', function() {
                var startDateValue = $(this).val();
                $(this).closest('.dynamic-field').find('.end-date').val(startDateValue);
            });

            $('input[name="start_date"]').on('change', function() {
                var startDateValue = $(this).val();
                $('input[name="end_date"]').val(startDateValue);
            });

        });

        function initializeSelect2() 
        {
            $('.select3').select2();
            $('.category-select3').select2({
                tags: true
            });
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

