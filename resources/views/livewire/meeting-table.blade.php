@canAccess('index','meetings')
<div id="meeting-table-root">
    {{-- ===================== FILTER CARD ===================== --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <h4 class="mb-0 font-weight-bold"><i class="fas fa-handshake mr-2 text-primary"></i>Daftar Rapat</h4>

            <div class="d-flex align-items-center ml-auto flex-wrap">
                @if($googleReadyChecked)
                    @if ($googleConnected)
                        <span class="badge badge-success px-3 py-2 mb-1 mr-1">
                            <i class="fas fa-check-circle mr-1"></i> Google Calendar
                        </span>
                    @else
                        <a href="{{ route('google.auth') }}" class="btn btn-warning btn-sm mb-1 mr-1">
                            <i class="fas fa-link mr-1"></i> Hubungkan Google Calendar
                        </a>
                    @endif
                @endif

                @canAccess('create','meetings')
                <a href="{{ route('meeting.create') }}" class="btn btn-primary btn-sm mb-1 mr-1">
                    <i class="fas fa-plus mr-1"></i> Buat Rapat
                </a>
                @endcanAccess
            </div>
        </div>

        <div class="card-body pb-2">
            {{-- Row 1: Search + Meeting Type --}}
            <div class="row g-2 mb-2">
                <div class="col-md-4">
                    <label class="form-label text-muted small mb-1"><i class="fas fa-search mr-1"></i>Cari Rapat</label>
                    <input type="text"
                           class="form-control form-control-sm"
                           wire:model.debounce.400ms="search"
                           placeholder="Nama atau agenda...">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1"><i class="fas fa-tag mr-1"></i>Jenis Rapat</label>
                    <select class="form-control form-control-sm" wire:model.lazy="meetingType">
                        <option value="">Semua Jenis</option>
                        <option value="online">🌐 Rapat Online</option>
                        <option value="google_meet">📹 Google Meet</option>
                        <option value="offline">🏢 Rapat Offline</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label text-muted small mb-1"><i class="fas fa-calendar-alt mr-1"></i>Rentang Tanggal</label>
                    <div class="input-group input-group-sm" wire:ignore>
                        <input type="text"
                               id="meeting-date-range"
                               class="form-control form-control-sm"
                               placeholder="Pilih rentang tanggal..."
                               autocomplete="off"
                               readonly>
                        <input type="hidden" id="meeting-date-start">
                        <input type="hidden" id="meeting-date-end">
                        <div class="input-group-append">
                            <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-muted"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: User Filter + Action Buttons --}}
            <div class="row g-2 align-items-end">
                <div class="col-md-7" wire:ignore>
                    <label class="form-label text-muted small mb-1"><i class="fas fa-users mr-1"></i>Filter Pengguna</label>
                    <select id="meeting-user-filter"
                            class="form-control form-control-sm"
                            multiple
                            style="width:100%">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ in_array($user->id, $userIds) ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5 d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-secondary btn-sm mb-1 mr-1" wire:click="resetFilters">
                        <i class="fas fa-times mr-1"></i>Reset
                    </button>

                    @canAccess('export','meetings')
                    <button type="button" id="btn-meeting-download" class="btn btn-success btn-sm mb-1 mr-1">
                        <i class="fas fa-file-excel mr-1"></i>Download Excel
                    </button>
                    @endcanAccess
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TABLE CARD ===================== --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center align-middle" style="width:4%">#</th>
                            <th class="align-middle" style="width:18%">Nama Rapat</th>
                            <th class="align-middle" style="width:24%">Agenda</th>
                            <th class="align-middle text-center" style="width:10%">Tanggal</th>
                            <th class="align-middle text-center" style="width:12%">Waktu</th>
                            <th class="align-middle text-center" style="width:16%">Jenis Rapat</th>
                            <th class="align-middle text-center" style="width:16%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($meetings as $index => $meeting)
                            <tr>
                                <td class="text-center align-middle">{{ $meetings->firstItem() + $index }}</td>
                                <td class="align-middle font-weight-bold">
                                    {{ Str::limit($meeting->meeting_name, 35) }}
                                </td>
                                <td class="align-middle text-muted small">
                                    {!! Str::limit(strip_tags($meeting->meeting_agenda), 100) !!}
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-light border">
                                        <i class="fas fa-calendar-day mr-1 text-primary"></i>
                                        {{ \Carbon\Carbon::parse($meeting->start_date)->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td class="text-center align-middle small">
                                    <i class="fas fa-clock mr-1 text-secondary"></i>
                                    {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
                                </td>
                                <td class="text-center align-middle">
                                    @php
                                        $typeLabel = match(strtolower($meeting->meeting_type ?? '')) {
                                            'online'      => ['icon' => 'fas fa-globe', 'badge' => 'badge-primary',   'text' => 'Rapat Online'],
                                            'offline'     => ['icon' => 'fas fa-building','badge' => 'badge-secondary','text' => 'Rapat Offline'],
                                            'google_meet' => ['icon' => 'fab fa-google',  'badge' => 'badge-success',  'text' => 'Google Meet'],
                                            default       => ['icon' => 'fas fa-question', 'badge' => 'badge-dark',    'text' => 'Tidak Diketahui'],
                                        };

                                        $isActiveRecurrence = false;
                                        if ($meeting->meetingRecurrence && $meeting->meetingRecurrence->is_active) {
                                            $isActiveRecurrence = true;
                                        } elseif ($meeting->generatedFromRecurrence && $meeting->generatedFromRecurrence->is_active) {
                                            $isActiveRecurrence = true;
                                        }
                                    @endphp
                                    <span class="badge {{ $typeLabel['badge'] }}">
                                        <i class="{{ $typeLabel['icon'] }} mr-1"></i>{{ $typeLabel['text'] }}
                                    </span>
                                    @if($isActiveRecurrence)
                                        <br>
                                        <span class="badge badge-warning mt-1" title="Rapat Berulang Aktif">
                                            <i class="fas fa-sync-alt mr-1"></i>Rutin
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        @canAccess('show','meetings')
                                        <a href="{{ route('meeting.show', $meeting->slug) }}"
                                           class="btn btn-info btn-xs"
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcanAccess

                                        @canAccess('edit','meetings')
                                        <a href="{{ route('meeting.edit', $meeting->slug) }}"
                                           class="btn btn-primary btn-xs"
                                           title="Edit Rapat">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcanAccess

                                        @canAccess('destroy','meetings')
                                        <form action="{{ route('meeting.destroy', $meeting->slug) }}"
                                              method="POST"
                                              style="display:inline;"
                                              class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcanAccess
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                    Tidak ada rapat yang ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($meetings->hasPages())
            <div class="px-3 py-2 border-top">
                {{ $meetings->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endcanAccess
