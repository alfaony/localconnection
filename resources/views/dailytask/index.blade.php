@extends('adminlte::page')

@section('content_header')
    <h1>Daftar Tugas Harian</h1>
@stop

@section('content')
<div class="card p-3">
    <div class="card-body col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('import'))
        <div class="alert alert-success mt-3">Import Tugas Berhasil</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Tugas Berhasil Diperbarui</div>
        @endif
        @if(Session::get('report'))
        <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan Laporan</div>
        @endif
        @if(Session::get('delete'))
        <div class="alert alert-success mt-3">Tugas Berhasil Terhapus</div>
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

    <div class="col-md-12">
        @canAccess('create','dailytasks')
        <a href="{{ route('dailytask.create') }}" class="btn btn-primary mb-3 col-md-2"><i class="fa fa-plus"></i><span> Tugas</span></a>
        @endcanAccess
        @canAccess('template','dailytasks')
        <a href="{{ route('dailytask.template') }}" class="btn btn-info mb-3 col-md-2"><i class="fa fa-plus"></i><span> Import Tugas</span></a>
        @endcanAccess
    </div>
    @canAccess('index','dailytasks')
    <form method="GET" action="{{ route('dailytask.index') }}" class="mb-3">
        <div class="row align-items-end gy-2">
            <div class="col-12 col-md-2">
                <div class="form-group">
                    <label for="task">Task</label>
                    <select class="form-control select2" id="task" name="task">
                        <option value="all">All</option>
                        @foreach ($taskTimeFrame as $status => $value)
                            <option value="{{ $status }}" {{ request('task') == $status ? 'selected' : '' }}>{{ ucfirst($value) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="user">User</label>
                    <select class="form-control select2" name="user">
                        <option value="" disabled selected>-- Select User--</option>
                        <option value="all">All</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->name }}" {{ request('user') == $user->name ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="division">Division</label>
                    <select class="form-control select2"  name="division">
                        <option value="">-- Divisi --</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->name }}" {{ request('division') == $division->name ? 'selected' : '' }}>{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="division">Main Proyek</label>
                    <select class="form-control select2" name="daily_task_project">
                        <option value="">-- Main Proyek --</option>
                        @foreach ($dailyTaskProjects as $dailyTaskProject)
                            <option value="{{ $dailyTaskProject->name }}" {{ request('daily_task_project') == $dailyTaskProject->name ? 'selected' : '' }}>{{ $dailyTaskProject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control select2" id="status" name="status">
                        <option value="">Select Status</option>
                        @foreach ($taskStatuss as $status)
                            <option value="{{ $status->name }}" {{ request('status') == $status->name ? 'selected' : '' }}>{{ ucfirst($status->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="date_range">Date Range</label>
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ request('start_date') && request('end_date') ? request('start_date').' - '.request('end_date') : '' }}">
                        <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-2">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search" value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="form-group">
                    <label for="user">Order By</label>
                    @php
                        $order = request('sort', 'desc');
                    @endphp
                        <select name="sort" class="form-control">
                            <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Created By</option>
                            <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Created By</option>
                        </select>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                        <button type="button" onclick="window.location.href='{{ route('dailytask.index') }}?task=all&start_date={{ \Carbon\Carbon::now()->subMonth()->startOfMonth()->format('d-m-Y') }}&end_date={{ \Carbon\Carbon::now()->addMonth()->endOfMonth()->format('d-m-Y') }}'" class="btn btn-secondary"><i class="fa fa-times"></i> Show All</button>
                        @canAccess('export','dailytasks')
                        <button type="button" class="btn btn-success" onclick="exportFilteredData('xlsx')">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportFilteredData('csv')">
                            <i class="fa fa-file-excel"></i> Export CSV
                        </button>
                        @endcanAccess
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endcanAccess
    

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-auto">Tanggal</th>
                    <th class="col-auto">Status</th>
                    <th class="col-auto">Tugas</th>
                    <th class="col-auto">Main Proyek</th>
                    <th class="col-auto">Data Proyek</th>
                    <th class="col-auto">Poin</th>
                    <th class="col-auto">Dibuat</th>
                    <th class="col-auto">Ditugaskan</th>
                    <th class="col-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dailyTasks as $index => $dailytask)
                    @php
                        $startDate = \Carbon\Carbon::parse($dailytask->start_date);
                        $endDate = \Carbon\Carbon::parse($dailytask->end_date);
                        $isOverdue = $dailytask->isOverdue();
                        $nextTask = $dailyTasks->get($index + 1); // Get the next task in the list
                    @endphp
                    <tr id="task-row-{{ $dailytask->id }}"> <!-- Added ID for each row -->
                        <td>
                            <span class="{{ $isOverdue ? 'text-danger' : '' }}">
                                {{ $dailytask->dateShow }}
                            </span>
                        </td>
                        <td>
                        @switch($dailytask->taskStatus->name)
                            @case('todo')
                                <i class="fa fa-list-alt"></i> Todo
                                @break
                            @case('doing')
                                <i class="fa fa-hourglass-start"></i> Doing
                                @break
                            @case('in review')
                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                @break
                            @case('not complete')
                                <i class="fa fa-times-circle" style="color: red;"></i> Not Complete
                                @break
                            @case('complete')
                                @php
                                    $latestCompleteRecord = $dailytask->statusRecords
                                        ->filter(fn($r) => optional($r->taskStatus)->name === \App\Schemas\ParamSchema::COMPLATE)
                                        ->sortByDesc('created_at')
                                        ->first();
                                @endphp

                                @if ($latestCompleteRecord)
                                    <i class="fa fa-check" style="color: green;"></i> Complete
                                    <br>
                                    <small>{{ $latestCompleteRecord->created_at->format('d M Y H:i') }}</small>
                                @endif
                                @break
                            @default
                                {{ $dailytask->taskStatus->name }}
                        @endswitch
                        </td>
                        <td class="name-cell">
                            <p>{!! $dailytask->head ? $dailytask->nameShow.'  <i class="fa fa-arrow-left"></i>  '. Str::limit($dailytask->head->name,50) : $dailytask->nameShow !!}</p>
                        </td>
                        <td class="name-cell">
                            {{ $dailytask->project ? $dailytask->project->name : '' }}
                        </td>
                        <td class="name-cell">
                            {{ $dailytask->dataProject ? $dailytask->dataProject->title : '' }}
                        </td>
                        <td>{{ $dailytask->point == 0 ? "-" : $dailytask->point }}</td>
                        <td class="name-cell">{{ $dailytask->user->name ?? '' }}</td>
                        <td class="name-cell">{{ $dailytask->assign->name ?? '' }}</td>
                        <td>
                            @if(!$dailytask->approved)
                            @if($isShow)
                            <button class="btn btn-info btn-sm show-popup-btn" data-slug-next="{{ $nextTask ? $nextTask->slug : '' }}" id="btn-show-{{ $dailytask->id }}" data-task-id="{{ $dailytask->id }}" data-task-slug="{{ $dailytask->slug }}">
                                <i class="fa fa-eye"></i>
                            </button>
                            @endif
                            <form action="{{ route('dailytask.destroy', $dailytask->slug) }}" method="POST" style="display:inline-block;">
                                @if(($dailytask->user_id == Auth::user()->id) || (Auth::user()->role->name == \App\Schemas\RoleSchema::MANAGER && $dailytask->taskStatus->name == \App\Schemas\ParamSchema::COMPLATE))
                                @if($isEdit)
                                <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                @endif
                                @csrf
                                @method('DELETE')
                                @if($isDestroy)
                                <input type="hidden" name="redirect" value="back">
                                <button type="button" class="btn btn-danger delete-button btn-sm"><i class="fa fa-trash"></i></button>
                                @endif
                                @endif
                            </form>
                            @else
                            @if($isShow)
                            <button class="btn btn-info btn-sm show-popup-btn" data-slug-next="{{ $nextTask ? $nextTask->slug : '' }}" id="btn-show-{{ $dailytask->id }}" data-task-id="{{ $dailytask->id }}" data-task-slug="{{ $dailytask->slug }}">
                                <i class="fa fa-eye"></i>
                            </button>
                            @endif
                            @if($isEdit)
                            @if($isApprovement)
                            <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                            @endif
                            @endif

                            @if(Auth::user()->role->name == \App\Schemas\RoleSchema::ROOT || Auth::user()->role->name == \App\Schemas\RoleSchema::ADMIN || Auth::user()->role->name == \App\Schemas\RoleSchema::MANAGER)
                            <form action="{{ route('dailytask.destroy', $dailytask->slug) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                @if($isDestroy)
                                <input type="hidden" name="redirect" value="back">
                                <button type="button" class="btn btn-danger delete-button btn-sm"><i class="fa fa-trash"></i></button>
                                @endif
                            </form>
                            @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $dailyTasks->withQueryString()->links('vendor.pagination.bootstrap-4') }}

</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="sidePopup" aria-labelledby="sidePopupLabel">
    <div class="offcanvas-header">
        <div class="d-flex justify-content-end w-100">
            <div class="me-auto">
                <button class="btn btn-info text-white btn-sm me-2">Edit</button>
                <button class="btn btn-info text-white btn-sm">Detail</button>
            </div>
            <button type="btn button" class="btn-close" id="btn-offcanvas-closed" data-bs-dismiss="offcanvas" aria-label="Close"><i class="btn btn-trash"></i></button>
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
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.js"></script>
<script src="https://unpkg.com/browser-image-compression/dist/browser-image-compression.js"></script>

@canAccess('approvement','dailytasks')
@canAccess('checkDivisionQuota','dailytasks')
<script>
    let selectedDivisionId = null;

    $(document).on('input', '#pointInput', function () {
        let point = parseInt($(this).val());

        if (isNaN(point) || point <= 0) {
            // Poin kosong atau <= 0 → tidak perlu divisi
            $('#divisionSelect').val('').trigger('change'); // kosongkan dropdown
            $('#divisionSelect').closest('.form-group').addClass('d-none');
            $('#quotaInfo').addClass('d-none');
            $('#quotaWarning').addClass('d-none');

            // Enable tombol submit
            $('#submitApprovement, #submitAndContinue').prop('disabled', false);
            return;
        }

        // Poin valid → tampilkan divisi
        $('#divisionSelect').closest('.form-group').removeClass('d-none');

        // Reset info kuota
        $('#quotaInfo').addClass('d-none').text('');
        $('#quotaWarning').addClass('d-none').text('');

        selectedDivisionId = $('#divisionSelect').val();
        
        if (!selectedDivisionId) {
            $('#submitApprovement, #submitAndContinue').prop('disabled', true);
            return;
        }

        // Lanjutkan cek kuota
        checkQuota(point, selectedDivisionId);
    });

    $(document).on('change', '#divisionSelect', function () {
        selectedDivisionId = $(this).val();
        let point = parseInt($('#pointInput').val());

        if (!selectedDivisionId || isNaN(point) || point <= 0) {
            $('#submitApprovement, #submitAndContinue').prop('disabled', true);
            return;
        }

        checkQuota(point, selectedDivisionId);
    });

    function checkQuota(point, divisionId) {
        $.ajax({
            url: '{{ route("dailytask.checkDivisionQuota") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                point: point,
                division_id: divisionId,
                exclude_task_id: '{{ $dailytask->id ?? null }}' // agar pengecekan edit tetap akurat
            },
            success: function (res) {
                if (res.status === 'fail') {
                    $('#quotaWarning').removeClass('d-none').text(res.message);
                    $('#quotaInfo').addClass('d-none');
                    $('#submitApprovement, #submitAndContinue').prop('disabled', true);
                } else {
                    $('#quotaWarning').addClass('d-none');
                    $('#quotaInfo').removeClass('d-none').text('Sisa kuota: ' + res.remaining + ' poin');
                    $('#submitApprovement, #submitAndContinue').prop('disabled', false);
                }
            },
            error: function () {
                $('#quotaWarning').removeClass('d-none').text('Terjadi kesalahan saat cek kuota.');
                $('#submitApprovement, #submitAndContinue').prop('disabled', true);
            }
        });
    }
</script>
@endcanAccess
@endcanAccess

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
                        $('#task-row-' + response.dailytask.id).replaceWith(response.htmlTable);
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
    $(document).on('change', '#mediaReport', async function(event) {

        var maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
        var files = this.files;
        var validFiles = [];

        // Loop through selected files
        for (var i = 0; i < files.length; i++) {
            if (files[i].size > maxFileSize) {
                alert('File ' + files[i].name + ' terlalu besar dan akan dihapus. Batas maksimal 10 MB');
            } else {
                if (files[i].type.startsWith('image/')) {
                    // Compress the image if it's an image file
                    try {
                        const compressedFile = await compressImage(files[i]);
                        validFiles.push(compressedFile);
                    } catch (error) {
                        console.error('Error during image compression:', error);
                    }
                } else {
                    // Non-image files are added without compression
                    validFiles.push(files[i]);
                }
            }
        }

        // Clear the input and add back the valid files (compressed or original)
        $(this).val('');
        var dataTransfer = new DataTransfer();
        for (var j = 0; j < validFiles.length; j++) {
            dataTransfer.items.add(validFiles[j]);
        }
        this.files = dataTransfer.files;

        console.log("Valid files after processing:", validFiles);
    });

    // Function to compress image using the browser-image-compression library
    async function compressImage(file) {
        const options = {
            maxSizeMB: 1, // Max size 1MB for the compressed image
            maxWidthOrHeight: 1024, // Set max width or height to 1024px
            useWebWorker: true // Enable Web Worker for better performance
        };

        const compressedBlob = await imageCompression(file, options);
        console.log(`Compressed ${file.name} from ${file.size / 1024 / 1024}MB to ${compressedBlob.size / 1024 / 1024}MB`);

        // Convert the compressed Blob back into a File
        const compressedFile = new File([compressedBlob], file.name, { type: file.type });
        return compressedFile;
    }
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
                                    $("#btn-offcanvas-closed").click();

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
@canAccess('export','dailytasks')
<script>
    function exportFilteredData(format) 
    {
        let params = new URLSearchParams(window.location.search); // Get the current query string parameters
        let url = '{{ route('dailytask.export') }}' + '?format=' + format + '&' + params.toString();

        window.location.href = url;
    }
</script>
@endcanAccess

<script>
    $(document).ready(function () {
        $('.select2').select2();
    });
    
    $(document).ready(function () {
        // Initialize Daterangepicker
        $('#date_range').daterangepicker({
            autoUpdateInput: false, // Prevents the input from being automatically populated
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear' // Adds a clear button to the picker
            }
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        });

        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Capture the date range selection
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $('#start_date').val(picker.startDate.format('DD-MM-YYYY'));
            $('#end_date').val(picker.endDate.format('DD-MM-YYYY'));
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-button').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const form = this.closest('form');

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
            });
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.css" rel="stylesheet">
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
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .container {
        background-color: #fff;
        border-radius: 5px;
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
</style>
<!-- Improvement CSS -->
<style>
    .table-responsive td, .table-responsive th {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-responsive p {
        margin-bottom: 0;
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
