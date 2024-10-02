@extends('adminlte::page')

@section('title', 'Task Tracking')

@section('content')
<div class="card-body col-md-12">
    @if(Session::get('report'))
    <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan Laporan</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Tugas Berhasil Dihapus</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
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
</div>
<div id="accordion">
@canAccess('fetchusertask', 'project_dashboards')
  <div class="card p-3 mt-3">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Pantauan Tugas
        </button>
      </h5>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
        <div class="card-body">
            <div class="accordion" id="taskAccordion">
                <form method="GET" action="{{ route('projectdashboard.index') }}" class="mb-3">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="date_range">Divisi</label>
                            <select class="form-control select2" name="division_id">
                                <option value="" selected>-- Divisi --</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date_range">Select Date Range</label>
                            <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ request('start_date') && request('end_date') ? request('start_date').' - '.request('end_date') : '' }}">
                            <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="row mb-4">
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
                <div class="card">
                    <div class="card-header" id="headingOverdue">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOverdue" aria-expanded="true" aria-controls="collapseOverdue">
                                Overdue Tasks {{ $startDate && $endDate ? "(".$startDate->format('d-m-Y')." - ".$endDate->format('d-m-Y').")" : '' }}
                            </button>
                        </h5>
                    </div>
                    <div id="collapseOverdue" class="collapse show" aria-labelledby="headingOverdue" data-parent="#taskAccordion">
                        <div class="card-body">
                            <!-- Task Status Overview Section -->
                            <div class="row mb-4">
                                <div class="col-md-2 text-center">
                                    <div class="status-box bg-primary text-white p-2 rounded">
                                        <i class="fas fa-list-alt fa-1x"></i>
                                        <h6 class="mt-1">{{ $totalCounts['todo'] }}</h6>
                                        <p class="small m-0">To Do</p>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="status-box bg-warning text-white p-2 rounded">
                                        <i class="fas fa-hourglass-start fa-1x"></i>
                                        <h6 class="mt-1">{{ $totalCounts['doing'] }}</h6>
                                        <p class="small m-0">Doing</p>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="status-box bg-info text-white p-2 rounded">
                                        <i class="fas fa-eye fa-1x"></i>
                                        <h6 class="mt-1">{{ $totalCounts['in_review'] }}</h6>
                                        <p class="small m-0">In Review</p>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="status-box bg-danger text-white p-2 rounded">
                                        <i class="fas fa-times-circle fa-1x"></i>
                                        <h6 class="mt-1">{{ $totalCounts['not_complate'] }}</h6>
                                        <p class="small m-0">Not Complete</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart Section -->
                            <canvas id="overdueTasksChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingUpcoming">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseUpcoming" aria-expanded="false" aria-controls="collapseUpcoming">
                                Upcoming Tasks
                            </button>
                        </h5>
                    </div>
                    <div id="collapseUpcoming" class="collapse" aria-labelledby="headingUpcoming" data-parent="#taskAccordion">
                        <div class="card-body">
                            <canvas id="upcomingTasksChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <h3>Tasks : <span id="userName"></span></h3>
                <div id="userTasks"></div>
            </div>
        </div>
    </div>

  </div>
  @endcanAccess
  <div class="card mt-2">
    <div class="card-header" id="headingTwo">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            Pantauan Visi 
        </button>
      </h5>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
      <div class="card-body">
        <h2 class="mb-4">Task Tracking</h2>
            <div class="card-body accordion" id="visionAccordion">
                @foreach($visions as $vision)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" id="headingVision{{ $vision->id }}">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseVision{{ $vision->id }}" aria-expanded="true" aria-controls="collapseVision{{ $vision->id }}">
                                Visi: {{ $vision->vision }} 
                                ({{ $vision->missions->sum(function($mission) {
                                    return $mission->objectives->sum(function($objective) {
                                        return $objective->keyResults->sum(function($keyResult) {
                                            return $keyResult->dailyTasks->count();
                                        });
                                    });
                                }) }} Task)
                            </button>
                        </h5>

                    </div>

                    <div id="collapseVision{{ $vision->id }}" class="collapse" aria-labelledby="headingVision{{ $vision->id }}" data-parent="#visionAccordion">
                        <div class="card-body">
                            <div class="accordion" id="missionAccordion{{ $vision->id }}">
                                <h5 class="p-3">Misi</h5>
                                @foreach($vision->missions as $mission)
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center" id="headingMission{{ $mission->id }}">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseMission{{ $mission->id }}" aria-expanded="false" aria-controls="collapseMission{{ $mission->id }}">
                                                {{ $mission->mission }} ({{ $mission->objectives->sum(function($objective) {
                                                    return $objective->keyResults->sum(function($keyResult) {
                                                        return $keyResult->dailyTasks->count();
                                                    });
                                                }) }} Task)
                                            </button>
                                        </h5>
                        
                                    </div>
                                    <div id="collapseMission{{ $mission->id }}" class="collapse" aria-labelledby="headingMission{{ $mission->id }}" data-parent="#missionAccordion{{ $vision->id }}">
                                        <div class="card-body">
                                            <h5 class="p-3">Objective</h5>
                                            <div class="accordion" id="objectiveAccordion{{ $mission->id }}">
                                                @foreach($mission->objectives as $objective)
                                                <div class="card">
                                                    <div class="card-header d-flex justify-content-between align-items-center" id="headingObjective{{ $objective->id }}">
                                                        <h5 class="mb-0">
                                                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseObjective{{ $objective->id }}" aria-expanded="false" aria-controls="collapseObjective{{ $objective->id }}">
                                                                {{ $objective->name }} ({{ $objective->keyResults->sum(function($keyResult) {
                                                                    return $keyResult->dailyTasks->count();
                                                                }) }} Task)
                                                            </button>
                                                        </h5>
                                        
                                                    </div>
                                                    <div id="collapseObjective{{ $objective->id }}" class="collapse" aria-labelledby="headingObjective{{ $objective->id }}" data-parent="#objectiveAccordion{{ $mission->id }}">
                                                        <div class="card-body">
                                                            <h5 class="p-3" >Key Result</h5>
                                                            <div class="accordion" id="keyResultAccordion{{ $objective->id }}">
                                                                @foreach($objective->keyResults as $keyResult)
                                                                <div class="card">
                                                                    <div class="card-header d-flex justify-content-between align-items-center" id="headingKeyResult{{ $keyResult->id }}">
                                                                        <h5 class="mb-0">
                                                                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseKeyResult{{ $keyResult->id }}" aria-expanded="false" aria-controls="collapseKeyResult{{ $keyResult->id }}">
                                                                                {{ $keyResult->result }} ({{ $keyResult->dailyTasks->count() }} Task)
                                                                            </button>
                                                                        </h5>
                                                        
                                                                    </div>
                                                                    <div id="collapseKeyResult{{ $keyResult->id }}" class="collapse" aria-labelledby="headingKeyResult{{ $keyResult->id }}" data-parent="#keyResultAccordion{{ $objective->id }}">
                                                                        <div class="card-body">
                                                                            <ul class="list-group">
                                                                                    <div class="table-responsive">
                                                                                        <table class="table table-striped table-bordered">
                                                                                            <thead class="thead-light">
                                                                                                <tr>
                                                                                                    <th>
                                                                                                        #
                                                                                                    </th>
                                                                                                    <th>Tugas</th>
                                                                                                    <th>Tanggal</th>
                                                                                                    <th>Status</th>
                                                                                                    <th>Ditugaskan</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                            @foreach($keyResult->dailyTasks as $dailyTask)
                                                                                    @php
                                                                                        $isOverdueSub = $dailyTask->isOverdue();
                                                                                    @endphp
                                                                                                <tr>
                                                                                                    <td>
                                                                                                        @canAccess('show', 'dailytasks')
                                                                                                        <a href="{{ route('dailytask.show', $dailyTask->slug) }}">{{ Str::limit($dailyTask->name) }}</a>
                                                                                                        @endcanAccess
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <span class="{{ $isOverdueSub ? 'text-danger' : '' }}">
                                                                                                            {{ $dailyTask->dateShow }}
                                                                                                        </span>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        @switch($dailyTask->taskStatus->name)
                                                                                                            @case('todo')
                                                                                                                <i class="fa fa-list-alt"></i>
                                                                                                                @break
                                                                                                            @case('doing')
                                                                                                                <i class="fa fa-hourglass-start"></i>
                                                                                                                @break
                                                                                                            @case('in review')
                                                                                                                <i class="fa fa-eye" style="color: green;"></i>
                                                                                                                @break
                                                                                                            @case('not complete')
                                                                                                                <i class="fa fa-times-circle" style="color: red;"></i>
                                                                                                                @break
                                                                                                            @case('complete')
                                                                                                                <i class="fa fa-check" style="color: green;"></i>
                                                                                                                @break
                                                                                                            @default
                                                                                                                {{ $dailyTask->taskStatus->name }}
                                                                                                        @endswitch
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        {{ $dailyTask->assign ? $dailyTask->assign->name : '' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="sidePopup" aria-labelledby="sidePopupLabel">
    <div class="offcanvas-header">
        <div class="d-flex justify-content-end w-100">
            <div class="me-auto">
                <button class="btn btn-info text-white btn-sm me-2">Edit</button>
                <button class="btn btn-info text-white btn-sm">Detail</button>
            </div>
            <button type="btn button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"><i class="btn btn-trash"></i></button>
        </div>
    </div>
  
  <div class="offcanvas-body">

  </div>
</div>

<div id="loader" class="loading-overlay" style="display:none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
@endsection
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@canAccess('show','dailytasks')
<script>
    $(document).on('click', '.show-popup-btn', function() {
        var taskSlug = $(this).data('task-slug');
        var nextSlug = $(this).data('slug-next'); // Next task slug if available
        let url = "{{ route('dailytask.show', ':id') }}";
        url = url.replace(':id', taskSlug);

        // Show loader
        $('#loader').show();

        $.ajax({
            url: url,
            method: 'GET',
            data: {
                next_slug: nextSlug // Send the next slug with the request
            },
            success: function(response) {
                if (response.success) {
                    // Update the content inside the sidebar and show the offcanvas
                    $('#sidePopup .offcanvas-body').html(response.html);
                    $('#sidePopup .offcanvas-header').html(response.htmlHead); // Optional header update
                    let bsOffcanvas = new bootstrap.Offcanvas(document.getElementById('sidePopup'));
                    bsOffcanvas.show(); // Show the sidebar

                    // Re-initialize any necessary plugins (like tooltips or editors)
                    if ($('#sidePopup .offcanvas-body').find('#description_note').length > 0) {
                        generateThriveEditor("note");
                    }

                    if ($('#sidePopup .offcanvas-body').find('#dropzone').length > 0) {
                        initializeDropzone()
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Task not found.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while fetching task details.'
                });
            },
            complete: function() {
                // Hide the loader after the AJAX request completes
                $('#loader').hide();
            }
        });
    });

    $(document).ready(function () 
    {
    // Event listener for the copy-link button
    $(document).on('click', '.copy-link-button', function () {
        var taskSlug = $(this).data('task-slug');  // Get the slug from data-slug attribute
        
        if(taskSlug)
        {
            var link = "{{ route('dailytask.show',':id') }}"
            link = link.replace(':id', taskSlug);
    
            navigator.clipboard.writeText(link).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Link Tersalin',
                    text: 'Link berhasil disalin ke clipboard',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }, function(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyalin',
                    text: 'Terjadi kesalahan saat menyalin link'
                });
            });
        }
    });
});


    function reloadPopupContent(taskSlug) 
    {
        let url = "{{ route('dailytask.show', ':id') }}";
        url = url.replace(':id', taskSlug);
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#sidePopup .offcanvas-body').html(response.html); // Update the popup content
                    $('#sidePopup .offcanvas-header').html(response.htmlHead); // Update the popup header
                    if(response.dailytask)
                    {
                        var button = $('#btn-show-' + response.dailytask.id);
                        var taskNo = button.data('task-no') ?? 0;
                        
                        let newRow = $(response.htmlTable); // Mengambil row baru dari response

                        if(taskNo != 0)
                        {
                            taskNo = taskNo;
                            newRow.prepend(`<td>${taskNo}</td>`);
                        }
                        // Gantikan row lama dengan row baru yang sudah dimodifikasi
                        $('#task-row-' + response.dailytask.id).replaceWith(newRow);
                    }
                    if ($('#sidePopup .offcanvas-body').find('#description_note').length > 0) 
                    {
                        generateThriveEditor("note");
                    }

                    let bsOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sidePopup'));
                    bsOffcanvas.show(); // Keep the popup open
                } else {
                    alert('Task not found.');
                }
            },
            error: function() {
                alert('Error fetching task details.');
            }
        });
    }

    function initializeDropzone() 
    {
        Dropzone.autoDiscover = false; // Prevent automatic Dropzone initialization

        var taskSlug = $('#task_slug').val(); // Get task slug from the hidden input
        var myDropzone = new Dropzone("#dropzone", {
            url: "{{ route('dailytask.report', ':slug') }}".replace(':slug', taskSlug),
            paramName: "media", // Name of the file input field
            maxFilesize: 1, // Maximum file size in MB
            addRemoveLinks: true,
            autoProcessQueue: false, // Prevent automatic processing of files
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Pass CSRF token
            },
            acceptedFiles: "image/*,application/pdf,.doc,.docx", // Accept certain file types
            init: function () {
                var dropzoneInstance = this; // Store Dropzone instance for later use

                // Handle the form submission
                $('#submitReport').on('click', function (e) {
                    e.preventDefault();

                    // Validate the note field
                    if ($('#description_note').val().trim() === "") {
                        alert("Please provide a note.");
                        return;
                    }

                    // If there are files to upload, process Dropzone queue
                    if (dropzoneInstance.getQueuedFiles().length > 0) {
                        dropzoneInstance.processQueue();
                    } else {
                        // If no files, submit the form manually
                        submitFormWithoutFiles();
                    }
                });

                // Append additional data to the file upload request
                this.on("sending", function (file, xhr, formData) {
                    formData.append("_method", "PUT");
                    formData.append("note", $('#description_note').val());
                    formData.append("request", "index");
                });

                // Handle the completion of the queue
                this.on("queuecomplete", function () {
                    alert("Task report submitted successfully.");
                    window.location.reload(); // Reload the page on completion
                });

                // Handle errors
                this.on("error", function (file, response) {
                    alert("Error uploading file: " + response);
                });
            }
        });
    }

    // Function to handle form submission without file uploads
    function submitFormWithoutFiles() {
        var taskSlug = $('#task_slug').val(); // Get task slug
        var note = $('#description_note').val(); // Get the note
        var formData = new FormData();
        formData.append("_method", "PUT");
        formData.append("note", note);
        formData.append("request", "index");

        $.ajax({
            url: "{{ route('dailytask.report', ':slug') }}".replace(':slug', taskSlug),
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Pass CSRF token
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                alert("Task report submitted successfully.");
                window.location.reload(); // Reload the page on success
            },
            error: function (response) {
                alert("Error submitting the report.");
            }
        });
    }
</script>
@endcanAccess
@canAccess('statuschange','dailytasks')
<script>
    $(document).on('click', '#start-task-btn', function() {
        var taskSlug = $(this).data('slug-task'); // Get the slug from the data attribute

        if (!taskSlug) 
        {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Task slug is missing!',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        $('#loader').show();

        let url = "{{ route('dailytask.statuschange',':id') }}";
        url = url.replace(':id',taskSlug)

        $.ajax({
            url:url,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Task status updated successfully!',
                        timer: 1000,
                        didOpen: () => {
                            Swal.showLoading();
                            const b = Swal.getHtmlContainer().querySelector('b');
                            timerInterval = setInterval(() => {
                                b.textContent = Swal.getTimerLeft();
                            }, 100);
                        },
                        willClose: () => {
                            clearInterval(timerInterval);
                            reloadPopupContent(taskSlug); // Function to reload the popup content
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update task status.',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        willClose: () => {
                            location.reload(); // Reload the page after the delay
                        }
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the task status.',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            },
            complete: function() {
                // Hide the loader once the request is complete
                $('#loader').hide();
            }
        });
    });
</script>
@endcanAccess
<script>
    // Use event delegation to ensure the change event is handled even when mediaReport is rendered dynamically
    $(document).on('change', '#mediaReport', function() {
        
        var maxFileSize = 1 * 1024 * 1024; // 1MB in bytes
        var files = this.files;
        var validFiles = [];

        for (var i = 0; i < files.length; i++) {
            if (files[i].size > maxFileSize) {
                alert('File ' + files[i].name + ' terlalu besar dan akan dihapus. Batas maksimal 1 Mb');
            } else {
                validFiles.push(files[i]);
            }
        }

        // Clear the input and add back the valid files
        $(this).val('');
        var dataTransfer = new DataTransfer();
        for (var j = 0; j < validFiles.length; j++) {
            dataTransfer.items.add(validFiles[j]);
        }
        this.files = dataTransfer.files;
    });
</script>
@canAccess('approvement','dailytasks')
<script>
    $(document).on('click', '#submitApprovement, #submitAndContinue', function(e) {
        e.preventDefault();

        // Show confirmation alert
        Swal.fire({
            title: 'Anda yakin?',
            text: "Anda tidak dapat membatalkan ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with form submission
                let isContinue = $(this).attr('id') === 'submitAndContinue';
                let nextTaskId = $('#submitAndContinue').data('next-id'); // Assuming you have the next task slug in a data attribute

                var formData = $('#approvementForm').serialize(); // Get all form data
                var slug = $('#submitApprovementSlug').val(); 
                let url = "{{ route('dailytask.approvement', ':id') }}";
                url = url.replace(':id', slug);
                
                $.ajax({
                    url: url,
                    method: 'PUT',
                    data: formData + '&_token=' + '{{ csrf_token() }}', // Include CSRF token in the data
                    beforeSend: function() {
                        // Show a loading spinner or disable the button during submission
                        $('#submitApprovement').attr('disabled', true).text('Processing...');
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Task approved successfully!',
                                timer: 1000,
                                showConfirmButton: false
                            }).then(() => {
                                console.log(isContinue, nextTaskId);
                                

                                if (isContinue && nextTaskId) 
                                {
                                    reloadPopupContent(slug); // Function to reload the popup content
                                    let bsOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sidePopup'));
                                    bsOffcanvas.hide();

                                    // After closing, trigger the click on the next task button
                                    setTimeout(function() {
                                        $("#btn-show-" + nextTaskId).click();
                                    }, 400); // Delay to ensure the popup closes before opening the next one
                                    
                                } else 
                                {
                                    reloadPopupContent(slug); // Function to reload the popup content
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to approve the task. Please try again.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred. Please try again.'
                        });
                    },
                    complete: function() {
                        // Re-enable the button and reset the text after submission
                        $('#submitApprovement').attr('disabled', false).text('Simpan Tugas');
                    }
                });
            }
        });
    });
</script>
@endcanAccess
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });

    // Initialize Daterangepicker
    $('#date_range').daterangepicker({
        autoUpdateInput: false, // Prevents the input from being automatically populated
        locale: {
            format: 'DD-MM-YYYY',
            cancelLabel: 'Clear', // Adds a clear button to the picker
        },
        maxDate:"{{ $beforeAday }}"
    });

    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
    });

    // Clear the date range when the user clicks on 'Clear'
    $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('#start_date').val(null); // Set start_date to null
        $('#end_date').val(null); // Set end_date to null
    });


    // Capture the date range selection
    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $('#start_date').val(picker.startDate.format('DD-MM-YYYY'));
        $('#end_date').val(picker.endDate.format('DD-MM-YYYY'));
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data for Overdue Tasks
    var overdueLabels = @json($overdueTasks->pluck('name'));
    var overdueData = @json($overdueTasks->pluck('daily_task_assigns_count'));
    
    // Data for Upcoming Tasks
    var upcomingLabels = @json($upcomingTasks->pluck('name'));
    var upcomingData = @json($upcomingTasks->pluck('daily_task_assigns_count'));

    // Overdue Tasks Chart
    var ctxOverdue = document.getElementById('overdueTasksChart').getContext('2d');
    var overdueTasksChart = new Chart(ctxOverdue, {
        type: 'bar',
        data: {
            labels: overdueLabels,
            datasets: [{
                label: 'Overdue Tasks',
                data: overdueData,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            onClick: function(e, elements) {
                if (elements.length > 0) {
                    var index = elements[0].index;
                    var userName = overdueLabels[index];
                    var userId = @json($overdueTasks->pluck('id'))[index];
                    console.log('User ID:', userId, 'User Name:', userName);
                    fetchUserTasks(userId, userName, 'overdue');
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    // Upcoming Tasks Chart
    var ctxUpcoming = document.getElementById('upcomingTasksChart').getContext('2d');
    var upcomingTasksChart = new Chart(ctxUpcoming, {
        type: 'bar',
        data: {
            labels: upcomingLabels,
            datasets: [{
                label: 'Upcoming Tasks',
                data: upcomingData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            onClick: function(e, elements) {
                if (elements.length > 0) {
                    var index = elements[0].index;
                    var userName = upcomingLabels[index];
                    var userId = @json($upcomingTasks->pluck('id'))[index];
                    fetchUserTasks(userId, userName,'upcoming');
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    function fetchUserTasks(userId, userName, filter = 'overdue') 
    {
        document.getElementById('userName').innerText = userName;
        document.getElementById('userTasks').innerHTML = 'Loading...';
        
        let url = `{{ route('fetchusertask', [':userId', ':filter']) }}`;
        url = url.replace(':userId', userId);
        url = url.replace(':filter', filter);

        let startDate = "{{ $startDate }}";
        let endDate = "{{ $endDate }}";

        if (startDate && endDate) {
            url += `?start_date=${startDate}&end_date=${endDate}`;
        }
        let no = 1;
        fetch(url.replace(':userId', userId))
            .then(response => response.json())
            .then(tasks => {
                let tasksHtml = '<table class="table table-bordered"><thead><tr><th>#</th><th>Tugas</th><th>Status</th><th>Tanggal</th><th>Main Proyek</th><th>Data Proyek</th><th>Dibuat</th><th>Ditugaskan</th><th>Action</th></tr></thead><tbody>';
                tasks.forEach(task => {
                    console.log(task);
                    
                    let statusIcon = '';
                    let url = '';
                    switch (task.task_status) {
                        case 'todo':
                            statusIcon = '<i class="fa fa-list-alt"></i>';
                            break;
                        case 'doing':
                            statusIcon = '<i class="fa fa-hourglass-start"></i>';
                            break;
                        case 'in review':
                            statusIcon = '<i class="fa fa-eye" style="color: green;"></i>';
                            break;
                        case 'not complete':
                            statusIcon = '<i class="fa fa-times-circle" style="color: red;"></i>';
                            break;
                        case 'complete':
                            statusIcon = '<i class="fa fa-check" style="color: green;"></i>';
                            break;
                        default:
                            statusIcon = task.task_status.name;
                    }
                    let nextTaskSlug = task.next_task_slug ? task.next_task_slug : '';
                    if(task)
                    {
                        // url = `<a href="${task.url}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>`;
                        url = `<button class="btn btn-info btn-sm show-popup-btn" data-task-no="${no}" data-slug-next="${nextTaskSlug}" id="btn-show-${task.task_id}" data-task-id="${task.task_id}" data-task-slug="${task.slug}">
                                <i class="fa fa-eye"></i>
                            </button>`;

                    }else
                    {
                        url = '-';
                    }
                    
                    if(task.is_overdue)
                    {
                        date_show = `<span class="text-danger"> ${task.date_show }</span>`;
                    }else
                    {
                        date_show = `<span> ${task.date_show}</span>`;
                    }


                    tasksHtml += `<tr id="task-row-${task.task_id}">
                                    <td>${no++}</td>
                                    <td>${task.name_show}</td>
                                    <td>${statusIcon} ${task.task_status}</td>
                                    <td>${date_show}</td>
                                    <td>${task.main_project}</td>
                                    <td>${task.data_project}</td>
                                    <td>${task.user_create}</td>
                                    <td>${task.user_assign}</td>
                                    <td>
                                        ${url}
                                    </td>
                                </tr>`;
                });
                tasksHtml += '</tbody></table>';
                document.getElementById('userTasks').innerHTML = tasksHtml;
            })
            .catch(error => {
                document.getElementById('userTasks').innerHTML = 'Error fetching tasks.';
                console.error('Error fetching tasks:', error);
        });
    }
});
</script>
<script>
    $(document).ready(function() {
        $('.collapse').on('show.bs.collapse', function () {
            $(this).parent().find('.rotate-icon').addClass('rotate');
        }).on('hide.bs.collapse', function () {
            $(this).parent().find('.rotate-icon').removeClass('rotate');
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Use event delegation to handle dynamically added elements
        document.body.addEventListener('click', function (event) {
            if (event.target.closest('.delete-button')) {
                event.preventDefault();
                const button = event.target.closest('.delete-button');
                const form = button.closest('form');

                Swal.fire({
                    title: 'Apakah Anda Yakin Hapus Data?',
                    text: "Data ini akan dihapus beserta child tasknya!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    });

    $(document).on('click', '#button-submitReport', function() 
    {
        const submitButton = document.getElementById('submitReport');
        const noteInput = document.getElementById('description_note');
        
        if (noteInput.value.trim() === '') 
        {
            event.preventDefault();  // Mencegah submit form
            Swal.fire({
                title: 'Error!',
                text: 'Catatan tidak boleh kosong!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }else
        {
            $("#submit-submitReport").click()
        }
    });
</script>


@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8); /* Transparent white background */
    z-index: 1050; /* Ensure it stays above other content */
    display: flex;
    justify-content: center;
    align-items: center;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}


.form-control-plaintext {
    white-space: normal; /* Allows text to wrap */
}
.side-popup {
    position: fixed;
    top: 0;
    right: -400px; /* Keep this hidden offscreen by default */
    width: 400px;
    height: 100%;
    background-color: #fff;
    box-shadow: -2px 0 5px rgba(0,0,0,0.5);
    z-index: 1000;
    transition: right 0.5s ease-in-out; /* Make the transition smoother */
    overflow-y: auto;
}

.side-popup-content {
    padding: 20px;
}

.close-btn {
    font-size: 24px;
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
}
</style>
<style>
.card-header .btn-link {
    color: #007bff;
    text-decoration: none;
}
.card-header .btn-link:hover {
    text-decoration: underline;
}
.card-header i {
    transition: transform 0.3s ease;
}
.card-header .collapsed .fa-chevron-right {
    transform: rotate(0);
}
.card-header[aria-expanded="true"] .fa-chevron-right {
    transform: rotate(90deg);
}
.list-group-item {
    border: none;
    padding-left: 2rem;
}
</style>
<style>
    .list-group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .task-details {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .task-details span {
        margin-right: 10px;
    }
    .task-actions {
        display: flex;
        align-items: center;
    }
    .task-actions form {
        margin: 0;
    }
    .task-actions a,
    .task-actions button {
        margin-right: 5px;
    }
    .select2-selection__choice
    {
        background-color: #007bff !important;
        border: 2px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 2px solid #007bff !important;
    }
    #userTasks 
    {
        max-height: 500px;
        overflow-y: auto;
        margin-top: 20px;
    }

    .accordion .card-header {
        cursor: pointer;
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
<style>
    /* Default list styling */
    .ql-editor ol,
    .ql-editor ul {
        padding-left: 1.5em;
    }

    /* Level 1 indentation */
    .ql-editor .ql-indent-1 {
        padding-left: 2em;
    }

    /* Level 2 indentation */
    .ql-editor .ql-indent-2 {
        padding-left: 3em;
    }

    /* Level 3 indentation */
    .ql-editor .ql-indent-3 {
        padding-left: 4em;
    }
</style>
@endsection
