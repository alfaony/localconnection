
<!-- Task Title -->
<div class="form-group row">
    <div class="col-md-10">
        <strong>
            @if($dailytask->head)
                <!-- Jika ada head, tampilkan sebagai tombol -->
                <button class="btn btn-link p-0" onclick="reloadPopupContent('{{ $dailytask->head->slug }}')">
                    {{ $dailytask->head->name }}
                </button>
                <span class="mx-2"> < </span>
                {{ $dailytask->name }}
            @else
                <!-- Jika tidak ada head, tampilkan nama tugas saja -->
                {{ $dailytask->name }}
            @endif
        </strong>
    </div>
</div>


<!-- Assigned -->
<div class="form-group row mb-3">
    <div class="col-md-6">
        <p class="form-control-plaintext"><strong>Ditugaskan:</strong></p>
    </div>
    <div class="col-md-6 mt-2">
        <span class="badge badge-info">{{ $dailytask->assign->name ?? 'N/A' }}</span>
    </div>
</div>

<!-- Tanggal -->
<div class="form-group row mb-3">
    <div class="col-md-6">
        <p class="form-control-plaintext"><strong>Tanggal:</strong></p>
    </div>
    <div class="col-md-6">
        <p class="form-control-plaintext {{ $isOverdue ? 'text-danger' : '' }}">{{ $dailytask->dateShow }}</p>
    </div>
</div>

<!-- Status Submit -->
@if($dailytask->status_submit)
<div class="form-group row mb-3">
    <div class="col-md-6">
        <p class="form-control-plaintext"><strong>Status Submit:</strong></p>
    </div>
    <div class="col-md-6">
        <p class="form-control-plaintext {{ $dailytask->status_submit == 'late' ? 'text-danger' : 'text-success' }}">
            {{ ucfirst($dailytask->status_submit) }}
        </p>
    </div>
</div>
@endif

<!-- Status Tugas -->
<div class="form-group row mb-3">
    <div class="col-md-6">
        <p class="form-control-plaintext"><strong>Status Tugas:</strong></p>
    </div>
    <div class="col-md-6">
        <p class="form-control-plaintext">
            @switch($dailytask->taskStatus->name)
                @case('backlog') <i class="fa fa-clipboard-list"></i> Backlog @break
                @case('todo') <i class="fa fa-list-alt"></i> Todo @break
                @case('doing') <i class="fa fa-hourglass-start"></i> Doing @break
                @case('in review') <i class="fa fa-eye text-success"></i> In Review @break
                @case('not complete') <i class="fa fa-times-circle text-danger"></i> Not Complete @break
                @case('complete') <i class="fa fa-check text-success"></i> Complete @break
                @default {{ $dailytask->taskStatus->name }}
            @endswitch
        </p>
    </div>
</div>

<!-- Tipe -->
<div class="form-group row mb-3">
    <div class="col-md-6">
        <p class="form-control-plaintext"><strong>Tipe:</strong></p>
    </div>
    <div class="col-md-6">
        <p class="form-control-plaintext">{{ $dailytask->type ? $dailytask->type->name : 'N/A' }}</p>
    </div>
</div>

<!-- Recurring Days -->
@if($dailytask->recurringRule)
<div class="form-group row">
    <label class="col-sm-4 col-form-label">Tugas Berulang:</label>
    <div class="col-sm-8">
        <div class="mt-2">

            {{-- by_day (weekly) --}}
            @if(!empty($dailytask->recurringRule->by_day))
                @php
                    $dayMap = [
                        'MO' => 'Senin', 'TU' => 'Selasa', 'WE' => 'Rabu',
                        'TH' => 'Kamis', 'FR' => 'Jumat', 'SA' => 'Sabtu', 'SU' => 'Minggu',
                    ];
                @endphp
                <div class="mb-2">
                    <strong>Hari dalam Minggu:</strong><br>
                    @foreach($dailytask->recurringRule->by_day as $day)
                        <span class="badge badge-info mr-1">{{ $dayMap[$day] ?? $day }}</span>
                    @endforeach
                </div>
            @endif

            {{-- by_month_day (monthly/yearly) --}}
            @if(!empty($dailytask->recurringRule->by_month_day))
                <div class="mb-2">
                    <strong>Tanggal dalam Bulan:</strong><br>
                    @foreach($dailytask->recurringRule->by_month_day as $day)
                        <span class="badge badge-primary mr-1">{{ $day }}</span>
                    @endforeach
                </div>
            @endif

            {{-- by_month (yearly) --}}
            @if(!empty($dailytask->recurringRule->by_month))
                @php
                    $monthMap = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                @endphp
                <div class="mb-2">
                    <strong>Bulan dalam Tahun:</strong><br>
                    @foreach($dailytask->recurringRule->by_month as $month)
                        <span class="badge badge-success mr-1">{{ $monthMap[$month] ?? $month }}</span>
                    @endforeach
                </div>
            @endif

            {{-- until --}}
            @if($dailytask->recurringRule->until)
                <div class="mb-2">
                    <strong>Hingga:</strong>
                    {{ \Carbon\Carbon::parse($dailytask->recurringRule->until)->translatedFormat('d F Y') }}
                </div>
            @endif

        </div>
    </div>
</div>
@endif

<!-- Deskripsi -->
<div class="accordion" id="accordionPanelsStayOpenExample">
  <div class="accordion-item">
    <h2 class="accordion-header" id="panelsStayOpen-headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
        Detail Tugas
      </button>
    </h2>
    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingOne">
      <div class="accordion-body">
          @if($dailytask->description)
          <div class="form-group row mb-3">
              <div class="col-md-12">
                  <p class="form-control-plaintext"><strong>Deskripsi Tugas:</strong></p>
              </div>
              <div class="col-md-12">
                  <div class="card">
                      <div class="card-body">
                          <div class="ql-editor" style="max-height: 40vh; overflow-y: auto; white-space: unset; padding: 0;">
                              {!! $dailytask->description !!}
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          @endif
          
          <!-- Task Media -->
          @if($dailytask->taskMedia->count())
          <div class="form-group">
              <label for="media">File Tugas:</label>
              <div class="row g-3" style="max-height: 200px; overflow-y: auto;">
                  @foreach($dailytask->taskMedia as $media)
                  <div class="col-md-4">
                      <div class="card me-2 mb-2">
                          <div class="card-body d-flex justify-content-between align-items-center">
                              <div>
                                  @if(strpos($media->file_type, 'image') !== false)
                                      <i class="fa fa-file-image" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                  @elseif(strpos($media->file_type, 'pdf') !== false)
                                      <i class="fa fa-file-pdf" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                  @elseif(strpos($media->file_type, 'msword') !== false || strpos($media->file_type, 'officedocument.wordprocessingml.document') !== false)
                                      <i class="fa fa-file-word" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                  @else
                                      <i class="fa fa-file" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                  @endif
                              </div>
                              <div>
                                  <div class="dropdown">
                                      <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $media->id }}" data-toggle="dropdown">
                                          <i class="fa fa-ellipsis-v"></i>
                                      </button>
                                      <div class="dropdown-menu">
                                          <a class="dropdown-item" href="{{ s3_asset(true,10, $media->file_path) }}" target="_blank">
                                              <i class="fa fa-download"></i> Lihat
                                          </a>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  @endforeach
              </div>
          </div>
          @endif
      </div>
    </div>
  </div>
</div>


<!-- Task Action -->
@if($dailytask->taskStatus->name == \App\Schemas\ParamSchema::TODO || $dailytask->taskStatus->name == \App\Schemas\ParamSchema::NOTCOMPLATE)
<div class="row mt-3">
    <div class="col-md-12">
        <button id="start-task-btn" class="btn btn-success" data-slug-task="{{ $dailytask->slug }}">Mulai Pekerjaan</button>
    </div>
</div>
@endif
<div class="row mt-3">
    @if($dailytask->taskStatus->name == \App\Schemas\ParamSchema::DOING)
        @canAccess('report','dailytasks')
            <h5>Laporan Tugas</h5>
            <form action="{{ route('dailytask.report', $dailytask->slug) }}" method="POST" enctype="multipart/form-data" id="reportForm">
                @method('PUT')
                @csrf
                <input type="hidden" name="request" value="index">
                <div class="form-group">
                    <label for="note">Catatan</label>
                    <input class="thriveEditor form-control" id="description_note" name="note" placeholder="yang akan dicetak di perjanjian" required />
                </div>
                <div class="form-group">
                    <label for="media">Upload</label>
                    <input type="file" id="mediaReport" name="media[]" class="form-control" multiple>
                </div>
                <button type="button" class="btn btn-primary" id="button-submitReport">Simpan Laporan</button>
                <button type="submit" class="btn btn-primary" id="submit-submitReport" style="display:none">Simpan Laporan</button>
            </form>

        @endcanAccess
    @endif
</div>
<!-- Approvement Section -->
@if($dailytask->taskStatus->name == \App\Schemas\ParamSchema::INREVIEW)
<div class="row mt-3">
    <div class="col-md-12">
        <h5>Laporan Catatan dan Media</h5>
    </div>
    <div class="col-md-12">
        <div class="accordion" id="accordionExample">
            <!-- Accordion Item 1 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Detail Laporan
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne">
                    <div class="accordion-body">
                        <label for="media">Laporan:</label>
                        <div class="card">
                            <div class="card-body">
                                @if($dailytask->report_note)
                                    <div class="ql-editor" style="white-space:unset; padding:0px 0px;">
                                        {!! $dailytask->report_note !!}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($dailytask->media->count())
                            <div class="form-group">
                                <label for="media">File Laporan:</label>
                                <div class="row g-3" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($dailytask->media as $media)
                                    <div class="col-md-4">
                                        <div class="card me-2 mb-2">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if(strpos($media->file_type, 'image') !== false)
                                                        <i class="fa fa-file-image" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                                    @elseif(strpos($media->file_type, 'pdf') !== false)
                                                        <i class="fa fa-file-pdf" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                                    @elseif(strpos($media->file_type, 'msword') !== false || strpos($media->file_type, 'officedocument.wordprocessingml.document') !== false)
                                                        <i class="fa fa-file-word" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                                    @else
                                                        <i class="fa fa-file" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $media->id }}" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="{{ s3_asset(true,10, $media->file_path) }}" target="_blank">
                                                                <i class="fa fa-download"></i> Lihat
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
    </div>

    <div class="col-md-12 mt-3">
        @canAccess('approvement', 'dailytasks')
        <h5>Penilaian dan Penyelesaian</h5>
        <form id="approvementForm" method="POST">
            @csrf
            <input type="hidden" name="slug" value="{{ $dailytask->slug }}" id="submitApprovementSlug">
            <div class="form-group">
                <label for="task_status">Status Tugas</label>
                <select name="task_status" class="form-control select2" required>
                    @foreach($approvement as $a)
                    <option value="{{ $a->id }}">{{ ucfirst($a->name) }}</option>
                    @endforeach
                </select>
            </div>
            @canAccess('checkDivisionQuota','dailytasks')
            <div class="form-group mt-2">
                <label for="point">Poin</label>
                <input type="number" name="point" id="pointInput" class="form-control" placeholder="Masukkan Poin">
            </div>

            <div id="divisionSection" class="form-group mt-2 d-none">
                <label for="task_status">Point Divisi</label>
                <select id="divisionSelect" name="division_id" class="form-control">
                    <option value="" selected>Pilih Divisi</option>    
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </select>
                <small id="quotaInfo" class="text-muted d-none"></small>
                <small id="quotaWarning" class="text-danger d-none">Poin melebihi kuota tersedia!</small>
            </div>
            @endcanAccess
            
            <div class="d-flex justify-content-start">
                <button type="button" id="submitApprovement" class="btn btn-success mt-3">Simpan Tugas</button>
                @if($dailytaskNext)
                <button type="button" id="submitAndContinue" data-next-id="{{ $dailytaskNext->id }}"class="btn btn-success mt-3 ml-1">Simpan Tugas dan Lanjutkan</button>
                @endif
            </div>
        </form>
        @endcanAccess
    </div>
</div>
@elseif($dailytask->taskStatus->name == \App\Schemas\ParamSchema::COMPLATE)
<div class="row">
    <div class="col-md-12">
        <h5>Informasi Pekerjaan</h5>
        <div class="accordion" id="accordionPanelsStayOpenExample">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                    Detail Laporan
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo">
                    <div class="accordion-body">
                        <label for="media">Laporan:</label>
                        <div class="card">
                            <div class="card-body">
                                @if($dailytask->report_note)
                                    <div class="ql-editor" style="white-space:unset; padding:0px 0px;">
                                        {!! $dailytask->report_note !!}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($dailytask->media->count())
                            <div class="form-group">
                                <label for="media">File Laporan:</label>
                                <div class="row g-3" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($dailytask->media as $media)
                                    <div class="col-md-4">
                                        <div class="card me-2 mb-2">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if(strpos($media->file_type, 'image') !== false)
                                                        <i class="fa fa-file-image" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                                    @elseif(strpos($media->file_type, 'pdf') !== false)
                                                        <i class="fa fa-file-pdf" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                                    @elseif(strpos($media->file_type, 'msword') !== false || strpos($media->file_type, 'officedocument.wordprocessingml.document') !== false)
                                                        <i class="fa fa-file-word" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                                    @else
                                                        <i class="fa fa-file" data-bs-toggle="tooltip" title="{{ basename($media->file_path) }}"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $media->id }}" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="{{ s3_asset(true,10, $media->file_path) }}" target="_blank">
                                                                <i class="fa fa-download"></i> Lihat
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-info mt-3">
            <i class="fa fa-check-circle"></i> Pekerjaan telah diselesaikan.
        </div>
        <div class="form-group">
            <label for="point">Poin yang Diberikan</label>
            <input type="number" name="point" class="form-control" value="{{ $dailytask->point }}" readonly>
        </div>
    </div>
</div>
@endif

@if($dailytaskChildCount != 0)
<div class="d-flex justify-content-end mt-3">
    <a href="{{ route('dailytask.show', $dailytask->slug) }}" class="btn btn-link">
        See {{ $dailytaskChildCount }} Childs
    </a>
</div>
@endif