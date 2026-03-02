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
                        <h5>Tugas 1</h5>
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="assignment_user_id">Objective</label>
                                            @canAccess('getresult','objectives')
                                            <select name="objective[]" class="form-control objective-select select2" required>
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
                                            <select class="form-control select2 project-select" name="project_id[]">
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
                                            <select name="assignment_user_id[]" class="form-control select2" required>
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
                                            <select name="type_id[]" id="type_id" class="form-control" required onchange="toggleRecurringPanel(this)">
                                                @foreach($types as $type)
                                                    <option value="{{ $type->id }}" {{ $type->is_default ? 'selected' : '' }}>{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- Checkbox Hari -->
                                        <div id="recurring-panel" class="bg-light p-3 rounded shadow-sm mb-3" style="display: none;">
                                            <h5 class="mb-3">Pengaturan Tugas Berulang</h5>

                                            <!-- Frekuensi (default: DAILY) -->
                                            <div class="form-group mb-3">
                                                <label for="recurring_frequency">Frekuensi</label>
                                                <select name="recurring[0][frequency]" id="recurring_frequency" class="form-control" onchange="handleFrequencyChange(this)">
                                                    <option value="DAILY" selected>Setiap Hari</option>
                                                    <option value="WEEKLY">Setiap Minggu</option>
                                                    <option value="MONTHLY">Setiap Bulan</option>
                                                    <option value="YEARLY">Setiap Tahun</option>
                                                </select>
                                            </div>

                                            <!-- WEEKLY -->
                                            <div class="form-group mb-3" id="by-day-group" style="display:none;">
                                                <label>Pilih Hari</label><br>
                                                @foreach($days as $code => $label)
                                                    <label class="mr-3">
                                                        <input type="checkbox" name="recurring[0][by_day][]" value="{{ $code }}"> {{ $label }}
                                                    </label>
                                                @endforeach
                                            </div>

                                            <!-- MONTHLY & YEARLY -->
                                            <div class="form-group mb-3" id="by-month-day-group" style="display:none;">
                                                <label>Tanggal dalam Bulan</label>
                                                <select id="by_month_day_select" name="recurring[0][by_month_day][]" class="form-control" multiple>
                                                    @foreach(range(1,31) as $day)
                                                        <option value="{{ $day }}">{{ $day }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">Pilih satu atau lebih tanggal.</small>
                                            </div>

                                            <!-- YEARLY -->
                                            <div class="form-group mb-3" id="by-month-group" style="display:none;">
                                                <label>Bulan dalam Tahun</label>
                                                <select name="recurring[0][by_month][]" id="by_month_select" class="form-control" multiple>
                                                    <option value="">Pilih Bulan</option>
                                                    @foreach(range(1,12) as $month)
                                                        <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->isoFormat('MMMM') }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- UNTIL -->
                                            <div class="form-group mb-3" id="recurring-until-group">
                                                <label>Berulang Sampai</label>
                                                <input type="date" name="recurring[0][until]" class="form-control">
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
                                
                                <div class="text-right">
                                    <button type="button" class="btn btn-danger remove-button btn-sm"><i class="fa fa-trash"></i> Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @canAccess('statuschange','dailytasks')
                {{--<button type="button" id="add-task-user" class="btn btn-info mb-2 add-button"><i class="fa fa-plus"></i> Tugas</button>--}}
                <button type="submit" class="btn btn-success mb-2"><i class="fa fa-save"></i> Simpan</button>
                @endcanAccess
            </form>
        </div>
    </div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    function toggleRecurringPanel(selectEl) 
    {
        const selectedText = selectEl.options[selectEl.selectedIndex]?.text.toLowerCase();
        const recurringPanel = document.getElementById('recurring-panel');
        const freqSelect = document.getElementById('recurring_frequency');

        console.log("here");
        
        if (selectedText?.includes('recurring')) {
            recurringPanel.style.display = 'block';

            // Set default to DAILY and trigger its behavior
            freqSelect.value = 'DAILY';
            handleFrequencyChange(freqSelect);

        } else {
            recurringPanel.style.display = 'none';

            // Reset all inside recurring-panel
            document.querySelectorAll('#recurring-panel input, #recurring-panel select').forEach(el => {
                if (el.type === 'checkbox') el.checked = false;
                else el.value = '';
            });
            handleFrequencyChange({ value: '' });
        }
    }

    function handleFrequencyChange(selectEl) 
    {
        const freq = selectEl.value;

        // Toggle tampilan sesuai frekuensi
        document.getElementById('by-day-group').style.display = freq === 'WEEKLY' ? 'block' : 'none';
        document.getElementById('by-month-day-group').style.display = freq === 'MONTHLY' ? 'block' : 'none';
        document.getElementById('by-month-group').style.display = freq === 'YEARLY' ? 'block' : 'none';
        document.getElementById('recurring-until-group').style.display = freq ? 'block' : 'none';

        // Inisialisasi Select2 sesuai konteks
        if (freq === 'MONTHLY') {
            $('#by_month_day_select').select2({
                placeholder: 'Pilih tanggal...',
                allowClear: true,
                width: '100%'
            });
        }

        if (freq === 'YEARLY') {
            $('#by_month_select').select2({
                placeholder: 'Pilih bulan...',
                allowClear: true,
                width: '100%'
            });
        }
    }
</script>
<script>
    $(document).ready(function() 
    {
        // initializeSelect2();
        $('.select2').select2({
            placeholder: 'Pilih',
            allowClear: true
        });
        $('.category-select2').select2();

        $('.attachment-input').on('change', function() {
            validateAttachments(this);
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
            nomor = newIndex + 1;

            let fieldHTML = `
            <div class="dynamic-field mb-3 shadow-sm mt-3">
                <!-- Objective and Project Section -->
                <h5>Tugas ${nomor}</h5>
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assignment_user_id">Objective</label>
                                    <select name="objective[]" class="form-control objective-select select3" required>
                                        <option value="" disabled selected>-- Pilih --</option>
                                        @foreach($objectives as $objective)
                                            <option value="{{ $objective->id }}">{{ $objective->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="keyresult-fields-container-${newIndex}"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id">Pilih Main Proyek</label>
                                    <select class="form-control select3 project-select" name="project_id[]">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="custom-fields-container-${newIndex}"></div>
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
                                        <input type="date" class="form-control start-date" name="start_date[]" placeholder="Mulai Tanggal" required>
                                        <span class="input-group-text">hingga</span>
                                        <input type="date" class="form-control end-date" name="end_date[]" placeholder="Sampai Tanggal" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="assignment_user_id">Ditugaskan</label>
                                    <select name="assignment_user_id[]" class="form-control select3" required>
                                        <option value="" selected disabled>Pilih Ditugaskan</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="category_id">Kategori</label>
                                    <select name="category_id[]" class="form-control select3 category-select3" required>
                                        <option value="" selected disabled>Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ ucfirst($category->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="type_id">Jenis</label>
                                    <select name="type_id[]" class="form-control select3" required>
                                        <option value="" selected disabled>Pilih Tipe</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="type_id">File</label>
                                    <input type="file" name="attachments_${nomor}[]" class="form-control attachment-input" multiple>
                                </div>
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
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-danger remove-button btn-sm"><i class="fa fa-trash"></i> Hapus</button>
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
            $('.select3').select2({
                placeholder: 'Pilih',
            });
            $('.category-select3').select2();

            $('.attachment-input').on('change', function() {
                validateAttachments(this);
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

        // Function to validate attachments
        function validateAttachments(input) {
            var maxSize = 100 * 1024 * 1024; // 100 MB
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

        if (selectType) {
            selectType.addEventListener('change', function() {
                // Ganti dengan UUID dari tipe recurring yang sesuai
                var recurringUUID = '{{ $dailyTaskTypeRecurring->id }}';
                
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
            const selectedType = this.value;
            const typeIdRecurring = "{{ $taskRecurring->id }}"; // Define your recurring type ID here

            if (selectedType == typeIdRecurring) {
                startDateInput.setAttribute('min', minDate);
                endDateInput.setAttribute('min', minDate);

                // Set Value
                startDateInput.value = minDate;
                endDateInput.value = minDate;
            } else {
                startDateInput.removeAttribute('min');
                endDateInput.removeAttribute('min');
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

