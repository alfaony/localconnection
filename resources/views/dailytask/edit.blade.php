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
<div class="container py-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2>Edit Tugas Harian</h2>
            <form action="{{ route('dailytask.update', $dailytask->slug) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="assignment_user_id">Tanggal</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="start_date" placeholder="Mulai Tanggal" value="{{ $dailytask->start_date }}" required>
                        <span class="input-group-text">hingga</span>
                        <input type="date" class="form-control" name="end_date" placeholder="Sampai Tanggal" value="{{ $dailytask->end_date }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="assignment_user_id">Ditugaskan</label>
                            <select name="assignment_user_id" class="form-control select2" required>
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
                                    <option value="{{ $category->name }}" {{ $dailytask->daily_task_category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type_id">Jenis</label>
                            <select name="type_id" class="form-control select2" required>
                                <option selected disabled>Pilih Tipe</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" {{ $dailytask->daily_task_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @canAccess('getcustomfield','daily_task_projects')
                        <div class="form-group">
                            <label for="project_id">Pilih Proyek</label>
                            <select id="project_id" name="project_id" class="form-control select2" onchange="loadCustomFields();">
                                <option disabled>Pilih Proyek</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ $dailytask->daily_task_project_id == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="custom-fields-container"></div>
                        @endcanAccess

                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Tugas</label>
                            <input type="text" name="name" class="form-control" value="{{ $dailytask->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" placeholder="yang akan dicetak di perjanjian" value="{!! $dailytask->description !!}"/>
                        </div>
                    </div>
                    @if(@$dailytask->point)
                    @canAccess('approvement','dailytasks')
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="points" class="form-label">Poin</label>
                            <input type="number" class="form-control" id="points" name="point" value="{{ old('point', isset($dailytask) ? $dailytask->point : '') }}" >
                            @error('point')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endcanAccess
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
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

    $(document).ready(function() 
    {
        $('.select2').select2();
        // Assume you have a dailyTaskId variable available or extract it from the form
        var dailyTaskId = "{{ $dailytask->id }}";
        loadCustomFields(dailyTaskId);
    });
</script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('.selectEdit2').select2({tags:true});

        $('input[name="start_date"]').on('change', function() {
            var startDateValue = $(this).val(); // Ambil nilai dari startDate
            $('input[name="end_date"]').val(startDateValue); // Set nilai startDate ke endDate
        });

        generateThriveEditor("description_description");

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
</style>
@endsection
