@extends('adminlte::page')

@section('content')

    <div class="col-md-12 p-3">
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
            <form action="{{ route('dailytask.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="dynamic-form-fields">
                        <div class="dynamic-field mb-3 shadow-sm mt-3">
                            <!-- Objective and Project Section -->
                            <div class="card mb-1">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="assignment_user_id">Objective</label>
                                                @canAccess('getresult','objectives')
                                                <input type="hidden" name="objective_division_id[]" class="objective-division-id-input" value="">
                                                <select name="objective[]" class="form-control objective-select select2" required>
                                                    <option value="" disabled selected>-- Pilih --</option>
                                                    @foreach($objectives as $objective)
                                                        @forelse($objective->divisions as $div)
                                                            <option value="{{ $objective->id }}" data-division-id="{{ $div->id }}">{{ ucfirst($objective->name) }} - {{ $div->name }}</option>
                                                        @empty
                                                            <option value="{{ $objective->id }}" data-division-id="">{{ ucfirst($objective->name) }}</option>
                                                        @endforelse
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
                                                <select class="form-control select2 project-select" name="project_id[]" required>
                                                    <option value="" disabled selected>-- Pilih --</option>
                                                    @foreach($projects as $projectData)
                                                        <option value="{{ $projectData->id }}" {{ @$project->id == $projectData->id ? 'selected' : '' }}>{{ ucfirst($projectData->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @endcanAccess
                                            </div>
                                            <div id="custom-fields-container-0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Task Details Section -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="assignment_user_id">Tanggal</label>
                                                <div class="input-group">
                                                    <input type="date" class="form-control start-date" name="start_date[]" placeholder="Mulai Tanggal">
                                                    <span class="input-group-text">hingga</span>
                                                    <input type="date" class="form-control end-date" name="end_date[]" placeholder="Sampai Tanggal">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="assignment_user_id">Ditugaskan</label>
                                                <select name="assignment_user_id[]" class="form-control select2">
                                                    <option value="" selected disabled>Pilih Ditugaskan</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="category_id">Kategori</label>
                                                <select name="category_id[]" class="form-control select2 category-select2" required>
                                                    <option value="" selected disabled>Pilih Kategori</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ ucfirst($category->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="type_id">Jenis Tugas</label>
                                                <select name="type_id[]" id="type_id" class="form-control" required>
                                                    <option value="" selected disabled>Pilih Tipe</option>
                                                    @foreach($types as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- Checkbox Hari -->
                                            <div id="day-checkboxes" style="display:none;">
                                                <label>Pilih Hari:</label>
                                                <div>
                                                    @foreach($days as $day => $value)
                                                    <input type="checkbox" name="days[]" value="{{ $day }}"> {{ ucfirst($value) }}
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="type_id">File</label>
                                                <input type="file" name="attachments_0[]" class="form-control attachment-input" multiple>
                                            </div>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="source" value="{{ $redirect }}">
                    <input type="hidden" name="slug" value="{{ $project->slug }}">
                    
                    @canAccess('statuschange','dailytasks')
                    <button type="submit" class="btn btn-success mb-2"><i class="fa fa-save"></i> Simpan</button>
                    @endcanAccess
                </form>
        </div>
    </div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@include('partials.objective-division-filter-js')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>

<script>
    $(document).ready(function() 
    {
        // initializeSelect2();
        $('.attachment-input').on('change', function() {
            validateAttachments(this);
        });
        loadCustomFields('{{ $project->id }}');

        $('.select2').select2({
            placeholder: 'Pilih',
            allowClear: true,
            width: '100%' // Adjust width as needed
        });
        $('.category-select2').select2({
            width: '100%' // Adjust width as needed
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
            var objective  = $(this).val();
            var $field     = $(this).closest('.dynamic-field');
            var index      = $field.index();
            var divisionId = $(this).find('option:selected').data('division-id') || '';

            if (objective) {
                $.ajax({
                    url: '{{ url('objective/getresult') }}/' + objective,
                    data: { index: index, division_id: divisionId },
                    type: 'GET',
                    success: function(data) {
                        $('#keyresult-fields-container-' + index).html(data);
                        initializeSelect2ForContainer(index);
                    }
                });
            } else {
                $('#keyresult-fields-container-' + index).html('');
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
            $('.select3').select2({
                placeholder: 'Pilih',
                width: '100%' // Adjust width as needed
            });
            $('.category-select3').select2({
                width: '100%' // Adjust width as needed
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

        function loadCustomFields(projectId) 
        {
            var projectId = projectId
            var url = '{{ url('daily_task_project/getcustomfield') }}/' + projectId;
            

            console.log(url);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) 
                {
                    $('#custom-fields-container-0').html(data);
                    $('.select2-single, .select2-multiple').select2({
                        width: '100%' // Adjust width as needed
                    }); // Re-initialize select2
                }
            });
        }

        // Function to validate attachments
        function validateAttachments(input) {
            var maxSize = 1 * 1024 * 1024; // 1 MB
            var files = input.files;
            var validFiles = new DataTransfer(); // DataTransfer object to hold valid files

            for (var i = 0; i < files.length; i++) {
                if (files[i].size > maxSize) {
                    alert('File ' + files[i].name + ' terhapus, Maksimal file 1 Mb');
                } else {
                    validFiles.items.add(files[i]); // Add valid files to DataTransfer object
                }
            }

            input.files = validFiles.files; // Update input with valid files
        }

</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var selectType = document.getElementById('type_id');
        var dayCheckboxes = document.getElementById('day-checkboxes');

        if (selectType) {
            selectType.addEventListener('change', function() {
                // Ganti dengan UUID dari tipe recurring yang sesuai
                var recurringUUID = '{{ $taskRecurring->id }}';
                
                if (this.value === recurringUUID) {
                    dayCheckboxes.style.display = 'block'; // Tampilkan checkbox hari
                } else {
                    dayCheckboxes.style.display = 'none'; // Sembunyikan checkbox hari
                }
            });
        } else {
            console.error('Element with id "type_id" not found.');
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type_id');
        const startDateInput = document.querySelector('.start-date');
        const endDateInput = document.querySelector('.end-date');
        const minDate = "{{ $minDate }}"; // This is the min date from the controller

        typeSelect.addEventListener('change', function () {
            console.log("here");
            
            const selectedType = this.value;
            const typeIdRecurring = "{{ $taskRecurring->id }}"; // Define your recurring type ID here

            if (selectedType == typeIdRecurring) {
                startDateInput.setAttribute('min', minDate);
                endDateInput.setAttribute('min', minDate);

                // Set Value
                startDateInput.value = minDate;
                endDateInput.value = minDate;

                startDateInput.setAttribute('required', 'required');
                endDateInput.setAttribute('required', 'required');
            } else {
                startDateInput.removeAttribute('min');
                endDateInput.removeAttribute('min');

                startDateInput.removeAttribute('required');
                endDateInput.removeAttribute('required');
            }
        });
    });
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

