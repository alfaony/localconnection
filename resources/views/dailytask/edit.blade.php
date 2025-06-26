@extends('adminlte::page')

@section('content')
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

    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Tugas Berhasil Diperbarui</div>
        @endif
        @if(Session::get('report'))
        <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('delete'))
        <div class="alert alert-success mt-3">Tugas Berhasil Terhapus</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="card shadow-sm p-3 mt-3">
        <div class="card-body">
            <h2>Edit Tugas Harian</h2>
            <form id="task-form" action="{{ route('dailytask.update', $dailytask->slug) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="dynamic-field mb-3 shadow-sm">
                    <!-- Objective and Project Section -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="objective_id">Objective</label>
                                        <select name="objective" id="objective_id" class="form-control objective-select select2" onchange="loadKeyResult();" required>
                                            <option selected disabled>Pilih Objective</option>
                                            @foreach($objectives as $objective)
                                                <option value="{{ $objective->id }}" {{ $dailytask->objective_id == $objective->id ? 'selected' : ''}} 
                                                    {{ ($dailytask->head)  && ($dailytask->objective_id != $objective->id) ?   'disabled' : '' }}>
                                                    {{ $objective->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="keyresult-fields-container"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="project_id">Pilih Main Proyek</label>
                                        @canAccess('getcustomfield','daily_task_projects')
                                        <select id="project_id" name="project_id" class="form-control select2" onchange="loadCustomFields();">
                                            <option selected disabled>Pilih Proyek</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}" {{ $dailytask->daily_task_project_id == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                        @endcanAccess
                                    </div>
                                    <div id="custom-fields-container"></div>
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
                                        <label for="start_date">Tanggal</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control start-date" name="start_date" placeholder="Mulai Tanggal" value="{{ $dailytask->start_date }}" {{ $dailytask->taskStatus->name != \App\Schemas\ParamSchema::BACKLOG ? 'required' : '' }} >
                                            <span class="input-group-text">hingga</span>
                                            <input type="date" class="form-control end-date" name="end_date" placeholder="Sampai Tanggal" value="{{ $dailytask->end_date }}" {{ $dailytask->taskStatus->name != \App\Schemas\ParamSchema::BACKLOG ? 'required' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="assignment_user_id">Ditugaskan</label>
                                        <select name="assignment_user_id" class="form-control select2" {{ $dailytask->taskStatus->name != \App\Schemas\ParamSchema::BACKLOG ? 'required' : '' }}>
                                            <option selected disabled>Pilih Ditugaskan</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $dailytask->assignment_user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="category_id">Kategori</label>
                                        <select name="category_id" class="form-control selectEdit2" required>
                                            <option selected disabled>Pilih Kategori</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ $dailytask->daily_task_category_id == $category->id ? 'selected' : '' }}>{{ ucfirst($category->name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @php
                                        $isRecurring = optional($dailytask->type)->name === 'Recurring';
                                        $recurring = optional($dailytask->recurringRule);
                                    @endphp

                                    <div class="form-group">
                                        <label for="type_id">Jenis</label>
                                        <select name="type_id" id="type_id" class="form-control" required onchange="toggleRecurringPanel(this)">
                                            <option value="" selected disabled>Pilih Tipe</option>
                                            @foreach($types as $type)
                                                <option value="{{ $type->id }}" {{ $dailytask->daily_task_type_id == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                     <!-- Checkbox Hari -->
                                     <div id="recurring-panel" style="{{ $isRecurring ? '' : 'display:none;' }}" class="bg-light p-3 rounded shadow-sm mb-3">
                                        <h5 class="mb-3">Pengaturan Tugas Berulang</h5>

                                        <div class="form-group mb-3">
                                            <label for="recurring_frequency">Frekuensi</label>
                                            <select name="recurring[frequency]" id="recurring_frequency" class="form-control" onchange="handleFrequencyChange(this)">
                                                <option value="DAILY" {{ $recurring && $recurring->frequency === "DAILY" ? 'selected' : '' }} >Setiap Hari</option>
                                                <option value="WEEKLY" {{ $recurring && $recurring->frequency === "WEEKLY" ? 'selected' : '' }}>Setiap Minggu</option>
                                                <option value="MONTHLY" {{ $recurring && $recurring->frequency === "MONTHLY" ? 'selected' : '' }}>Setiap Bulan</option>
                                                <option value="YEARLY" {{ $recurring && $recurring->frequency === "YEARLY" ? 'selected' : '' }}>Setiap Tahun</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3" id="by-day-group" style="display:none;">
                                            <label>Pilih Hari</label><br>
                                            @foreach($days as $code => $label)
                                                <label class="mr-2">
                                                    <input type="checkbox" name="recurring[by_day][]" value="{{ $code }}"
                                                        {{ in_array($code, $recurring->by_day ?? []) ? 'checked' : '' }}> {{ $label }}
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="form-group mb-3" id="by-month-day-group" style="display:none;">
                                            <label>Tanggal dalam Bulan</label>
                                            <select name="recurring[by_month_day][]" id="by_month_day_select" class="form-control" multiple>
                                                @foreach(range(1, 31) as $day)
                                                    <option value="{{ $day }}" {{ in_array($day, $recurring->by_month_day ?? []) ? 'selected' : '' }}>
                                                        {{ $day }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group mb-3" id="by-month-group" style="display:none;">
                                            <label>Bulan dalam Tahun</label>
                                            <select name="recurring[by_month][]" id="by_month_select" class="form-control" multiple data-placeholder="Pilih bulan">
                                                @foreach(range(1, 12) as $month)
                                                    <option value="{{ $month }}" {{ in_array($month, $recurring->by_month ?? []) ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::create()->month($month)->isoFormat('MMMM') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group mb-3" id="recurring-until-group">
                                            <label>Berulang Sampai</label>
                                            <input type="date" name="recurring[until]" value="{{ optional($recurring->until)->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Tugas</label>
                                        <input type="text" name="name" class="form-control" value="{{ $dailytask->name }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Deskripsi</label>
                                        <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" placeholder="yang akan dicetak di perjanjian" value="{{ $dailytask->description }}"/>
                                    </div>
                                </div>
                                @if(@$dailytask->approved)

                                @canAccess('approvement','dailytasks')
                                @canAccess('checkDivisionQuota','dailytasks')
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="pointInput">Poin</label>
                                        <input type="number" name="point" id="pointInput"
                                            class="form-control" placeholder="Masukkan Poin"
                                            value="{{ old('point', $dailytask->point) }}">
                                    </div>

                                    <div class="form-group" id="divisionWrapper">
                                        <label for="divisionSelect">Divisi</label>
                                        <select id="divisionSelect" name="division_id" class="form-control {{ $dailytask->point > 0 ? '' : 'd-none' }}">
                                            <option value="">Pilih Divisi</option>
                                            @foreach($divisions as $division)
                                                <option value="{{ $division->id }}" {{ $dailytask->division_id == $division->id ? 'selected' : '' }}>
                                                    {{ $division->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="pointSection" class="{{ $dailytask->point > 0 ? 'mt-2' : 'd-none' }}">
                                        <small id="quotaInfo" class="text-muted d-none"></small>
                                        <small id="quotaWarning" class="text-danger d-none">Poin melebihi kuota tersedia!</small>
                                    </div>
                                </div>
                                @endcanAccess
                                @endcanAccess
                                
                                @endif
                            </div>
                            
                            <div class="text-right">
                                <button type="submit" id="btn-submit" class="btn btn-primary">Simpan</button>
                                <button type="button" id="btn-confrim" class="btn btn-primary" style="display:none;">Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () 
    {
        const form = document.getElementById('task-form');

        if (form) {
            form.addEventListener('submit', function () {
                const freq = document.getElementById('recurring_frequency')?.value;

                if (freq !== 'WEEKLY') {
                    document.querySelectorAll('[name^="recurring[by_day]"]').forEach(el => el.remove());
                }

                if (freq !== 'MONTHLY' && freq !== 'YEARLY') {
                    document.querySelectorAll('[name^="recurring[by_month_day]"]').forEach(el => el.remove());
                }

                if (freq !== 'YEARLY') {
                    document.querySelectorAll('[name^="recurring[by_month]"]').forEach(el => el.remove());
                }
            });
        }
    });

    function toggleRecurringPanel(selectEl) {
        const selectedText = selectEl.options[selectEl.selectedIndex]?.text.toLowerCase();
        const recurringPanel = document.getElementById('recurring-panel');

        console.log("here");
        
        if (selectedText.includes('recurring')) {
            recurringPanel.style.display = 'block';
            handleFrequencyChange(document.getElementById('recurring_frequency'));
        } else {
            recurringPanel.style.display = 'none';
        }
    }

    function handleFrequencyChange(selectEl) 
    {
        const freq = selectEl.value;

        const byDay = document.getElementById('by-day-group');
        const byMonthDay = document.getElementById('by-month-day-group');
        const byMonth = document.getElementById('by-month-group');

        byDay.style.display = freq === 'WEEKLY' ? 'block' : 'none';
        byMonthDay.style.display = freq === 'MONTHLY' ? 'block' : 'none';
        byMonth.style.display = freq === 'YEARLY' ? 'block' : 'none';

        setTimeout(() => {
            if (freq === 'MONTHLY') {
                $('#by_month_day_select').select2({
                    placeholder: 'Pilih tanggal',
                    allowClear: true,
                    width: '100%'
                });
            }
            if (freq === 'YEARLY') {
                $('#by_month_select').select2({
                    placeholder: 'Pilih bulan',
                    allowClear: true,
                    width: '100%'
                });
            }
        }, 100);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const freqSelect = document.getElementById('recurring_frequency');
        if (freqSelect) {
            handleFrequencyChange(freqSelect);
        }
    });
</script>
@canAccess('approvement','dailytasks')
@canAccess('checkDivisionQuota','dailytasks')
<script>
    let selectedDivisionId = $('#divisionSelect').val();
    const excludeTaskId = "{{ $dailytask->id }}"; // Untuk pengecualian saat edit

    $(document).ready(function () {
        const initialPoint = parseInt($('#pointInput').val());
        if (!isNaN(initialPoint) && initialPoint > 0) {
            $('#divisionSelect').removeClass('d-none');
            $('#pointSection').removeClass('d-none');
            if (selectedDivisionId) {
                checkQuota(initialPoint, selectedDivisionId);
            }
        } else {
            $('#divisionSelect').addClass('d-none');
            $('#pointSection').addClass('d-none');
            toggleSubmitButtons(true);
        }
    });

    $(document).on('input', '#pointInput', function () {
        const point = parseInt($(this).val());

        if (isNaN(point) || point <= 0) {
            $('#divisionSelect').addClass('d-none');
            $('#quotaWarning, #quotaInfo').addClass('d-none');
            toggleSubmitButtons(false);
            return;
        }

        $('#divisionSelect').removeClass('d-none');
        selectedDivisionId = $('#divisionSelect').val();
        $('#pointSection').removeClass('d-none');

        if (selectedDivisionId) {
            checkQuota(point, selectedDivisionId);
        } else {
            toggleSubmitButtons(true);
        }
    });

    $(document).on('change', '#divisionSelect', function () {
        selectedDivisionId = $(this).val();
        const point = parseInt($('#pointInput').val());

        if (selectedDivisionId && point > 0) {
            checkQuota(point, selectedDivisionId);
        } else {
            toggleSubmitButtons(true);
        }
    });

    function toggleSubmitButtons(disable = true) {
        checkSubmit = "{{ $dailytask->submit ? 'true' : 'false' }}";
        
        if(checkSubmit == 'true') 
        {
            $('#submitApprovement, #btn-submit').attr('disabled', disable);
        }

    }

    function checkQuota(point, divisionId) {
        $.ajax({
            url: '{{ route("dailytask.checkDivisionQuota") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                point: point,
                division_id: divisionId,
                exclude_task_id: excludeTaskId,
            },
            success: function (res) {
                if (res.status === 'fail') {
                    $('#quotaWarning').removeClass('d-none').text(res.message);
                    $('#quotaInfo').addClass('d-none');
                    toggleSubmitButtons(true);
                } else {
                    $('#quotaWarning').addClass('d-none');
                    $('#quotaInfo').removeClass('d-none').text('Sisa kuota: ' + res.remaining + ' poin');
                    toggleSubmitButtons(false);
                }
            },
            error: function () {
                toggleSubmitButtons(true);
                $('#quotaWarning').removeClass('d-none').text('Terjadi kesalahan saat memeriksa kuota.');
            }
        });
    }
</script>
@endcanAccess
@endcanAccess

<script>
    
    function loadCustomFields(dailyTaskId = null) 
    {
        var projectId = $('#project_id').val();
        var url = '{{ url('daily_task_project/getcustomfield') }}/' + projectId;
        

        console.log(url);
        $.ajax({
            url: url,
            type: 'GET',
            data: 
            {
                dailyTaskId: dailyTaskId // Passing dailyTaskId to the server
            },
            success: function(data) {
                $('#custom-fields-container').html(data);
                $('.select2-single, .select2-multiple').select2(); // Re-initialize select2
            }
        });
    }

    function loadKeyResult(dailyTaskId = null) 
    {
        var objectiveId = $('#objective_id').val();
        console.log(objectiveId);
        var url = '{{ url('objective/getresult') }}/' + objectiveId;
        

        console.log(url);
        $.ajax({
            url: url,
            type: 'GET',
            data: 
            {
                dailyTaskId: dailyTaskId // Passing dailyTaskId to the server
            },
            success: function(data) {
                $('#keyresult-fields-container').html(data);
                $('.select2-single, .select2-multiple').select2(); // Re-initialize select2
            }
        });
    }

    $(document).ready(function() 
    {
        $('.select2').select2();
        // Assume you have a dailyTaskId variable available or extract it from the form
        var dailyTaskId = "{{ $dailytask->id }}";
        loadCustomFields(dailyTaskId);
        loadKeyResult(dailyTaskId);
    });
</script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('.selectEdit2').select2();

        $('input[name="start_date"]').on('change', function() {
            var startDateValue = $(this).val(); // Ambil nilai dari startDate
            $('input[name="end_date"]').val(startDateValue); // Set nilai startDate ke endDate
        });

        generateThriveEditor("description_description");

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type_id');
        const form = document.querySelector('form');
        const taskRecurring = '{{ $taskRecurring }}'; // Nilai taskRecurring dari server
        const initialTypeId = typeSelect.value; // Simpan nilai awal dari type_id

        function toggleRecurringDaysSection() {
            const selectedType = typeSelect.options[typeSelect.selectedIndex].text.toLowerCase();
        }

        // Jalankan saat halaman dimuat
        toggleRecurringDaysSection();

        // Jalankan saat user mengganti pilihan
        typeSelect.addEventListener('change', toggleRecurringDaysSection);
    });
</script>
<script>
    $("#type_id").change(function (e) { 
        e.preventDefault();
        const typeSelected = $(this).val();
        const taskRecurring = '{{ $taskRecurring->id }}'; // Nilai taskRecurring dari server
        if (typeSelected == taskRecurring) 
        {
            $('#btn-submit').hide();
            $('#btn-confrim').show();
        }
    });

    $("#btn-confrim").click(function (e) { 
        e.preventDefault();

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Tindakan ini akan membuat tugas secara berulang sesuai Waktu yang dipilih.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Buat Recurring!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#btn-submit").click();
            }
        });
        
    });
</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
        body 
        {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
        }
        .select2-selection__rendered 
        {
            line-height: 31px !important;
        }
        .select2-container .select2-selection--single 
        {
            height: 35px !important;
        }
        .select2-selection__arrow {
            height: 34px !important;
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
