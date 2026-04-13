@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
<div class="row mt-3">
    <div class="col-md-12">
        @if(Session::get('updateProfile'))
        <div class="alert alert-success shadow-sm" style="border-radius: 10px; border-left: 5px solid #198754;">
            <i class="fas fa-check-circle me-2"></i> Pengguna Berhasil Diperbarui
        </div>
        @endif

        @canAccess('reminderDashboard', 'weekly_reports')
        <div id="weekly-report-reminder" class="mb-2">
            <div class="text-center p-2 rounded" style="background: rgba(255,255,255,0.1); border: 1px dashed #6c757d;">
                <i class="fas fa-spinner fa-spin text-muted"></i> Memeriksa laporan mingguan...
            </div>
        </div>
        @endcanAccess

        <div id="vehicle-reminder-pic"></div>
        <div id="vehicle-reminder-manager"></div>
        <div id="vehicle-photo-reminder-pic"></div>
        <div id="reminder-letter-pic"></div>
        <div id="reminder-letter-manager"></div>

        {{-- LAPORAN PENGADUAN --}}
        <a href="https://forms.gle/sPs4j3L9oNrNkPmR6" target="_blank" rel="noopener"
           class="d-flex align-items-center mb-3 px-4 py-3 text-decoration-none laporan-banner">
            <div style="width:42px;height:42px;background:linear-gradient(135deg,#f093fb,#667eea);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 14px rgba(240,147,251,.4);">
                <i class="fas fa-bullhorn" style="color:#fff;font-size:1rem;"></i>
            </div>
            <div class="ms-3 flex-grow-1">
                <div class="fw-bold" style="font-size:.92rem;">Laporan Pengaduan Karyawan</div>
                <div style="font-size:.76rem;opacity:.75;">Sampaikan keluhan atau masukan secara anonim</div>
            </div>
            <i class="fas fa-external-link-alt ms-3" style="font-size:.82rem;opacity:.6;"></i>
        </a>

    </div>
</div>

@canAccess('dashboardReport','homes')
<div class="mb-2 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.85rem;">
    <i class="fas fa-user-astronaut me-1"></i> Player Status
</div>
<div class="row g-3 mb-4">

    {{-- ── PROFILE CARD ── --}}
    <div class="col-md-3 col-12">
        <div class="card border-0 shadow-lg hover-effect h-100" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:4px;background:linear-gradient(90deg,#667eea,#f093fb,#f5a623);"></div>
            <div class="card-body text-center p-3">
                <div class="position-relative d-inline-block mb-2">
                    <div style="width:80px;height:80px;background:linear-gradient(135deg,#667eea,#f093fb);border-radius:50%;padding:3px;margin:auto;">
                        <img src="{{ Auth::user()->avatar ? s3_asset(true,10,Auth::user()->avatar) : 'https://placehold.co/600x400?text=Avatar' }}"
                            class="rounded-circle" alt="Avatar"
                            style="width:74px;height:74px;object-fit:cover;background:#1a1a2e;">
                    </div>
                    <span id="profile-level-badge" class="position-absolute" style="bottom:-4px;right:-4px;font-size:1.1rem;" title="Level">🔶</span>
                </div>
                <h6 class="mb-1 fw-bold" style="color:#e0e0ff;">{{ Auth::user()->name }}</h6>
                <div class="d-inline-flex align-items-center px-2 py-1 mb-2 rounded-pill" style="background:rgba(102,126,234,.2);border:1px solid rgba(102,126,234,.4);">
                    <i class="bi bi-shield-fill-check me-1" style="color:#667eea;font-size:.7rem;"></i>
                    <span style="color:#a0a8d0;font-size:.72rem;">{{ ucfirst(Auth::user()->role->name) }}</span>
                </div>
                <div style="background:rgba(255,255,255,.07);border-radius:10px;padding:10px 12px;">
                    <div class="d-flex justify-content-between mb-1">
                        <small style="color:#a0a8d0;font-size:.7rem;">TOTAL XP</small>
                        <small id="profile-xp-label" style="color:#f093fb;font-weight:700;font-size:.7rem;">— XP</small>
                    </div>
                    <div style="height:6px;background:rgba(255,255,255,.1);border-radius:4px;overflow:hidden;">
                        <div id="profile-xp-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#667eea,#f093fb);border-radius:4px;transition:width .8s ease;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small style="color:#a0a8d0;font-size:.7rem;">SCORE</small>
                        <small class="fw-bold" style="color:#f5a623;font-size:.7rem;" id="currentScore">—</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── KANAN: STATS (compact) + GELAR ── --}}
    <div class="col-md-9 col-12">
        <div class="d-flex flex-column gap-3 h-100">

            {{-- 3 Stat Cards compact --}}
            <div class="row g-2">
                <div class="col-4">
                    <div class="card border-0 shadow-sm hover-effect gamified-stat-card" style="background:linear-gradient(135deg,#0f3443,#134e5e);border-radius:12px;">
                        <div class="top-glow" style="background:linear-gradient(90deg,#11998e,#38ef7d);"></div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-check2-circle text-success me-2" style="font-size:1.1rem;"></i>
                                <span style="color:#8ab4c0;font-size:.65rem;font-weight:700;letter-spacing:.04em;">TASK DONE</span>
                            </div>
                            <div class="fw-bold text-success" style="font-size:1.4rem;line-height:1;" id="totalTasksComplete">
                                <span class="placeholder col-8 placeholder-glow" style="font-size:.8rem;"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm hover-effect gamified-stat-card" style="background:linear-gradient(135deg,#0d1f3c,#162447);border-radius:12px;">
                        <div class="top-glow" style="background:linear-gradient(90deg,#4facfe,#00f2fe);"></div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-calendar-check text-info me-2" style="font-size:1.1rem;"></i>
                                <span style="color:#8ab4c0;font-size:.65rem;font-weight:700;letter-spacing:.04em;">CHECK-IN</span>
                            </div>
                            <div class="fw-bold text-info" style="font-size:1.4rem;line-height:1;" id="checkin_point_percentage">
                                <span class="placeholder col-8 placeholder-glow" style="font-size:.8rem;"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm hover-effect gamified-stat-card" style="background:linear-gradient(135deg,#2d1b69,#16213e);border-radius:12px;">
                        <div class="top-glow" style="background:linear-gradient(90deg,#f093fb,#f5a623);"></div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-graph-up me-2" style="color:#f093fb;font-size:1.1rem;"></i>
                                <span style="color:#8ab4c0;font-size:.65rem;font-weight:700;letter-spacing:.04em;">POINTS</span>
                            </div>
                            <div class="fw-bold" style="color:#f093fb;font-size:1.4rem;line-height:1;" id="totalPoints">
                                <span class="placeholder col-8 placeholder-glow" style="font-size:.8rem;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GELAR Section --}}
            @canAccess('userBadges','homes')
            <div class="flex-grow-1">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:14px;overflow:hidden;">
                    <div style="height:3px;background:linear-gradient(90deg,#f5a623,#f093fb,#667eea);"></div>
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span style="color:#a0a8d0;font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">🏅 Gelar</span>
                        </div>
                        <div id="gelar-loader" class="d-flex align-items-center justify-content-center flex-grow-1" style="min-height:110px;">
                            <div class="spinner-border spinner-border-sm" style="color:#f093fb;" role="status"></div>
                        </div>
                        <div id="gelar-container" class="d-none flex-wrap pt-4" style="gap:1.25rem 1.5rem;min-height:110px;overflow-x:auto;padding:4px 2px;"></div>
                        <div id="gelar-empty" class="d-none text-center flex-grow-1 d-flex align-items-center justify-content-center" style="min-height:110px;">
                            <div>
                                <div style="font-size:2rem;opacity:.3;">🏅</div>
                                <small style="color:#606880;">Belum ada gelar diterima</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcanAccess

        </div>
    </div>

</div>
@endcanAccess

@canAccess('leaderboard','homes')
<div class="mb-2 mt-4 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.85rem;">
    <i class="fas fa-trophy me-1"></i> Leaderboards & Hall of Fame
</div>
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:14px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#f5a623,#f53844);"></div>
            <div class="card-header border-0 d-flex align-items-center" style="background:transparent;">
                <i class="bi bi-trophy-fill me-2 fs-5" style="color:#f5a623;"></i>
                <span style="color:#e0e0ff;font-weight:600;">Top Score</span>
            </div>
            <div class="card-body p-2" style="height: 220px; overflow-y: auto;">
                <div id="leaderboard-loader" class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-warning" role="status"></div></div>
                <ol class="list-group list-group-flush d-none" id="leaderboard-list"></ol>
            </div>
        </div>
    </div>

    @canAccess('xpLeaderboard','homes')
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0 h-100 xp-leaderboard-card">
            <div class="card-header border-0 d-flex align-items-center justify-content-between xp-header-gradient">
                <span style="color:#e0e0ff;font-weight:600;"><i class="fas fa-star me-2 fs-5" style="color:#f093fb;"></i> Top 5 XP</span>
                @canAccess('leaderboard','employee_xps')
                <a href="{{ route('employee-xp.leaderboard') }}" class="btn btn-sm py-0 px-2 rounded-pill ml-auto" style="font-size:.75rem;background:rgba(102,126,234,.2);color:#a0c4ff;border:1px solid rgba(102,126,234,.4);">All</a>
                @endcanAccess
            </div>
            <div class="card-body p-2" id="xp-top5-container" style="height: 220px; overflow-y: auto;">
                <div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
    @endcanAccess

    @canAccess('overdueRanking','homes')
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:14px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#f7971e,#ffd200);"></div>
            <div class="card-header border-0 d-flex align-items-center" style="background:transparent;">
                <i class="bi bi-clipboard-check me-2 fs-5" style="color:#ffd200;"></i>
                <span style="color:#e0e0ff;font-weight:600;">Overdue In Review</span>
            </div>
            <div class="card-body p-2" style="height: 220px; overflow-y: auto;">
                <div id="overdue-inreview-loader" class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-warning" role="status"></div></div>
                <ol class="list-group list-group-flush d-none custom-dark-list" id="overdue-inreview-ranking"></ol>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:14px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#f5576c,#f093fb);"></div>
            <div class="card-header border-0 d-flex align-items-center" style="background:transparent;">
                <i class="bi bi-exclamation-triangle me-2 fs-5" style="color:#f5576c;"></i>
                <span style="color:#e0e0ff;font-weight:600;">Overdue Task</span>
            </div>
            <div class="card-body p-2" style="height: 220px; overflow-y: auto;">
                <div id="overdue-loader" class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-danger" role="status"></div></div>
                <ol class="list-group list-group-flush d-none custom-dark-list" id="overdue-ranking"></ol>
            </div>
        </div>
    </div>
    @endcanAccess
</div>
@endcanAccess

{{-- ════ ACTIVE CHALLENGES ════ --}}
@canAccess('activeChallenges','homes')
<div id="challenge-section" style="display:none;">
    <div class="mb-2 mt-4 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.85rem;">
        <i class="fas fa-fire me-1"></i> Challenge Aktif
    </div>
    <div id="challenge-loader" class="mb-3">
        <div class="d-flex align-items-center justify-content-center py-3"
             style="background:rgba(255,255,255,.04);border-radius:14px;border:1px dashed rgba(255,255,255,.1);">
            <div class="spinner-border spinner-border-sm me-2" style="color:#f093fb;" role="status"></div>
            <small style="color:#a0a8d0;">Memuat challenge...</small>
        </div>
    </div>
    <div id="challenge-container" class="mb-4"></div>
</div>
@endcanAccess

{{-- ════ ACTIVE EVENTS CALENDAR ════ --}}
@canAccess('activeEvents','homes')
<div id="event-section" style="display:none;">
    {{-- Header Section --}}
    <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
        <div class="text-uppercase fw-bold" style="letter-spacing:2px;font-size:.8rem;color:#667eea;text-shadow:0 0 12px rgba(102,126,234,.5);">
            <i class="fas fa-calendar-alt me-2"></i>Event Kalender
        </div>
        {{-- Week Navigation --}}
        <div class="d-flex align-items-center gap-2">
            <button onclick="shiftWeek(-1)"
                    style="background:rgba(102,126,234,.15);border:1px solid rgba(102,126,234,.4);color:#a5b4fc;border-radius:8px;padding:4px 10px;cursor:pointer;font-size:.75rem;transition:all .2s;"
                    onmouseover="this.style.background='rgba(102,126,234,.3)'" onmouseout="this.style.background='rgba(102,126,234,.15)'">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span id="event-week-label"
                  style="color:#285cc3;font-size:.78rem;min-width:130px;text-align:center;font-weight:600;"></span>
            <button onclick="shiftWeek(1)"
                    style="background:rgba(102,126,234,.15);border:1px solid rgba(102,126,234,.4);color:#a5b4fc;border-radius:8px;padding:4px 10px;cursor:pointer;font-size:.75rem;transition:all .2s;"
                    onmouseover="this.style.background='rgba(102,126,234,.3)'" onmouseout="this.style.background='rgba(102,126,234,.15)'">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    {{-- Loader --}}
    <div id="event-loader" class="mb-3">
        <div class="d-flex align-items-center justify-content-center py-4"
             style="background:rgba(102,126,234,.05);border-radius:14px;border:1px dashed rgba(102,126,234,.2);">
            <div class="spinner-border spinner-border-sm me-2" style="color:#667eea;" role="status"></div>
            <small style="color:#a0a8d0;">Memuat kalender event...</small>
        </div>
    </div>

    {{-- Calendar Card --}}
    <div id="event-calendar-wrap" style="display:none;margin-bottom:24px;">
        <div style="background: linear-gradient(160deg, rgba(71,71,71,0.85), rgba(28,29,32,0.95)), url('{{ asset('logo/event_background.png') }}') center/cover no-repeat; border-radius:16px;overflow:hidden;border:1px solid rgba(102,126,234,.2);box-shadow:0 0 30px rgba(102,126,234,.08); height: 300px">
            {{-- Day Header --}}
            <div id="event-day-header" style="display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid rgba(102,126,234,.15);background:rgba(102,126,234,.06);"></div>
            {{-- Today column highlight overlay rendered via JS --}}
            {{-- Event Rows --}}
            <div id="event-rows" class="custom-scrollbar" style="padding:12px 10px;display:flex;flex-direction:column;gap:7px;min-height:56px;max-height:350px;overflow-y:auto;position:relative;"></div>
        </div>
    </div>

    {{-- Empty state --}}
    <div id="event-empty" style="display:none;margin-bottom:24px;">
        <div class="text-center py-4"
             style="color:#55596e;font-size:.82rem;background:rgba(255,255,255,.02);border-radius:12px;border:1px dashed rgba(255,255,255,.06);">
            <i class="fas fa-calendar-times fa-lg d-block mb-2" style="color:#2d3561;"></i>
            Tidak ada event minggu ini
        </div>
    </div>
</div>
@endcanAccess

<div class="mb-2 mt-4 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.85rem;">
    <i class="fas fa-scroll me-1"></i> Quests & Team Status
</div>
<div class="row g-3 mb-4">
    @canAccess('meetingAgenda','homes')
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100 gamified-light-card" style="border-radius: 14px;">
            <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between pt-4 pb-2 px-4">
                <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-alt text-primary me-2"></i> Agenda Meeting</h5>
                <ul class="nav nav-pills nav-sm ml-auto" id="agendaTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active rounded-pill px-3 py-1 mb-1 mr-1" id="today-tab" data-bs-toggle="tab" type="button">Hari Ini</button></li>
                    <li class="nav-item ms-2"><button class="nav-link rounded-pill px-3 py-1 mb-1" id="week-tab" data-bs-toggle="tab" type="button">Minggu Ini</button></li>
                </ul>
            </div>
            <div class="card-body px-4 pb-4">
                @canAccess('store','meetings')
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('meeting.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"><i class="bi bi-plus-circle me-1"></i> Buat Agenda</a>
                </div>
                @endcanAccess
                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover table-borderless align-middle mb-0" id="agenda-table">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th width="30%">Agenda</th>
                                <th width="25%">Tanggal</th>
                                <th width="15%">Pukul</th>
                                <th>Tipe</th>
                                <th width="15%" class="text-center">Aksi/Lokasi</th>
                            </tr>
                        </thead>
                        <tbody><tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcanAccess

    <div class="col-lg-4">
        <div class="row g-3">
            @canAccess('listDayoff','homes')
            <div class="col-12">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
                    <div class="card-header bg-white border-0 pt-3 px-3">
                        <h6 class="fw-bold mb-0 text-secondary"><i class="fas fa-user-clock text-warning me-2"></i>User Cuti Hari Ini</h6>
                    </div>
                    <div class="card-body px-3" id="cuti-today-container">
                        <div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>
                    </div>
                </div>
            </div>
            @endcanAccess

            @canAccess('infoApprovementHr', 'dayoffs')
            <div class="col-6 col-lg-12">
                <div class="card border-0 shadow-sm rounded-3 bg-primary text-white hover-effect">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50 text-uppercase fw-bold mb-1">Menunggu HR</div>
                            <div class="h3 mb-0 fw-bold" id="count-hr"><span class="spinner-border spinner-border-sm"></span></div>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2"><i class="fas fa-user-tie fa-fw fs-4"></i></div>
                    </div>
                </div>
            </div>
            @endcanAccess

            @canAccess('infoApprovementFinance', 'dayoffs')
            <div class="col-6 col-lg-12">
                <div class="card border-0 shadow-sm rounded-3 bg-info text-white hover-effect">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50 text-uppercase fw-bold mb-1">Menunggu Finance</div>
                            <div class="h3 mb-0 fw-bold" id="count-finance"><span class="spinner-border spinner-border-sm"></span></div>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2"><i class="fas fa-coins fa-fw fs-4"></i></div>
                    </div>
                </div>
            </div>
            @endcanAccess
        </div>
    </div>
</div>

@canAccess('index','office_media')
<div class="mb-2 mt-4 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.85rem;">
    <i class="fas fa-photo-video me-1"></i> Guild Hall (Office Media)
</div>
<div class="card shadow-sm border-0 mb-4" style="border-radius: 14px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-camera text-danger me-2"></i> What's Happening Now!</h5>
            @canAccess('store','office_media')
            <button class="btn btn-outline-danger btn-sm rounded-pill px-3" data-toggle="modal" data-target="#uploadMomentModal"><i class="fas fa-upload me-1"></i> Upload</button>
            @endcanAccess
        </div>
        <div style="height: 250px; overflow-y: auto;" id="office-media-image-section" class="custom-scrollbar bg-light rounded-3 p-2 mb-4 border">
            <div class="loading-spiner-office d-flex justify-content-center align-items-center h-100" style="display: none;"><div class="spinner-border text-primary" role="status"></div></div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="fab fa-youtube text-danger me-2"></i> Nonton Youtube Kantor</h5>
            @canAccess('store','office_media')
            <button class="btn btn-outline-danger btn-sm rounded-pill px-3" data-toggle="modal" data-target="#youtubeEmbedModal"><i class="fas fa-link me-1"></i> Embed URL</button>
            @endcanAccess
        </div>
        <div style="height: 250px; overflow-y: auto;" id="office-media-youtube-section" class="custom-scrollbar bg-light rounded-3 p-2 border">
            <div class="loading-spiner-office d-flex justify-content-center align-items-center h-100" style="display: none;"><div class="spinner-border text-primary" role="status"></div></div>
        </div>
    </div>
</div>
@endcanAccess

@if(Auth::user()->role->name == \App\Schemas\RoleSchema::BM)
<div class="mb-2 mt-4 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.85rem;">
    <i class="fas fa-server me-1"></i> Command Center
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-12">
        @canAccess('showReport','homes')
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom pt-4 px-4">
                <h5 class="fw-bold"><i class="fas fa-project-diagram text-primary me-2"></i> Laporan Overview Proyek & Keuangan</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-2 col-6"><div class="card bg-primary text-white text-center p-3 h-100 border-0 rounded-3"><div class="small opacity-75 mb-1">Proyek Aktif</div><div class="h4 mb-0 fw-bold">{{ $totalActiveProjects }}</div></div></div>
                    <div class="col-md-2 col-6"><div class="card bg-success text-white text-center p-3 h-100 border-0 rounded-3"><div class="small opacity-75 mb-1">Pekerja Aktif</div><div class="h4 mb-0 fw-bold">{{ $totalActiveWorkers }}</div></div></div>
                    <div class="col-md-4 col-12"><div class="card bg-danger text-white p-3 h-100 border-0 rounded-3"><div class="small opacity-75 mb-1"><i class="fas fa-wallet me-1"></i> Anggaran Pembelian</div><div class="h4 mb-0 fw-bold">{{ 'Rp. '.number_format($totalPurchaseBudget,0,',','.') }}</div></div></div>
                    <div class="col-md-2 col-6"><div class="card bg-info text-white text-center p-3 h-100 border-0 rounded-3"><div class="small opacity-75 mb-1">Budget Proyek</div><div class="h5 mb-0 fw-bold">{{ 'Rp. '.number_format($activeProjectsBudget,0,',','.') }}</div></div></div>
                    <div class="col-md-2 col-6"><div class="card bg-secondary text-white text-center p-3 h-100 border-0 rounded-3"><div class="small opacity-75 mb-1">Budget Pekerja</div><div class="h5 mb-0 fw-bold">{{ 'Rp. '.number_format($activeEmployeeBudget,0,',','.') }}</div></div></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <a href="{{ route('quote.index') }}" class="text-decoration-none">
                            <div class="card bg-light border-0 shadow-sm hover-card rounded-3 p-3 d-flex flex-row align-items-center">
                                <div class="bg-warning text-dark p-3 rounded-circle me-3"><i class="fas fa-file-invoice-dollar fs-4"></i></div>
                                <div><div class="text-muted small fw-bold">TOTAL QUOTE</div><div class="h4 mb-0 text-dark fw-bold">{{ $totalQuote }}</div></div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('work-order.index') }}" class="text-decoration-none">
                            <div class="card bg-light border-0 shadow-sm hover-card rounded-3 p-3 d-flex flex-row align-items-center">
                                <div class="bg-success text-white p-3 rounded-circle me-3"><i class="fas fa-file-signature fs-4"></i></div>
                                <div><div class="text-muted small fw-bold">TOTAL SPK</div><div class="h4 mb-0 text-dark fw-bold">{{ $totalWorkOrder }}</div></div>
                            </div>
                        </a>
                    </div>
                </div>

                <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-exclamation-circle text-warning me-1"></i> Quote Tanpa SPK</h6>
                <form method="GET" action="{{ route('home') }}" class="mb-3 d-flex gap-2">
                    <input type="text" name="search_quote" class="form-control" placeholder="Cari No Quote..." style="max-width: 300px;">
                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-search"></i></button>
                </form>
                <div class="table-responsive rounded border">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr><th>No Quote</th><th>Total</th>@canAccess('downloadPdf','quotes')<th class="text-center">Aksi</th>@endcanAccess</tr>
                        </thead>
                        <tbody>
                            @forelse($quotesWithoutWorkOrder as $quote)
                            <tr>
                                <td>{{ $quote->number_result }}</td>
                                <td>Rp {{ number_format($quote->total, 0, ',', '.') }}</td>
                                @canAccess('downloadPdf','quotes')
                                <td class="text-center"><a href="{{ route('quote.download.pdf', $quote->slug) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i> Lihat</a></td>
                                @endcanAccess
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada quotes tanpa WorkOrder.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $quotesWithoutWorkOrder->withQueryString()->links('vendor.pagination.bootstrap-4') }}</div>
            </div>
        </div>
        @endcanAccess

        @canAccess('showReportPointDaily','homes')
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="fas fa-tasks text-success me-2"></i> Overview Pekerjaan Harian</h5>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('home') }}" class="bg-light p-3 rounded-3 mb-4 d-flex flex-wrap gap-3 align-items-end">
                    <div style="flex: 1; min-width: 200px;">
                        <label class="form-label small text-muted">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" value="{{ request('start_date') ?? $startDate->format('Y-m-d') }}">
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label class="form-label small text-muted">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" value="{{ request('end_date')  ?? $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter me-1"></i> Filter</button>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary"><i class="fa fa-redo"></i></a>
                    </div>
                </form>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6"><div class="border rounded-3 p-3 text-center"><div class="small text-muted mb-1">Poin Tugas</div><div class="h4 text-primary fw-bold mb-0">{{ $dailyTaskPoints }}</div></div></div>
                    <div class="col-md-3 col-6"><div class="border rounded-3 p-3 text-center"><div class="small text-muted mb-1">Poin Training</div><div class="h4 text-info fw-bold mb-0">{{ $trainingPoints }}</div></div></div>
                    <div class="col-md-3 col-6"><div class="border rounded-3 p-3 text-center"><div class="small text-muted mb-1">{{ Auth::user()->role->name == \App\Schemas\RoleSchema::SALES ? 'Poin Penjualan' : 'Poin Hak Cipta' }}</div><div class="h4 text-warning fw-bold mb-0">{{ $ipRightPoints }}</div></div></div>
                    <div class="col-md-3 col-6"><div class="border rounded-3 p-3 text-center bg-success text-white"><div class="small opacity-75 mb-1">Tugas Selesai</div><div class="h4 fw-bold mb-0">{{ $dailyTaskCompleteCount }}</div></div></div>
                </div>

                <div class="row g-2 text-center">
                    <div class="col"><div class="p-3 rounded-3" style="background: rgba(13,110,253,0.1); border: 1px solid #0d6efd;"><div class="fw-bold text-primary">TODO</div><div class="h3 mb-0">{{ $dailyTaskTodoCount }}</div></div></div>
                    <div class="col"><div class="p-3 rounded-3" style="background: rgba(13,202,240,0.1); border: 1px solid #0dcaf0;"><div class="fw-bold text-info">DOING</div><div class="h3 mb-0">{{ $dailyTasDoingCount }}</div></div></div>
                    <div class="col"><div class="p-3 rounded-3" style="background: rgba(255,193,7,0.1); border: 1px solid #ffc107;"><div class="fw-bold text-warning">IN REVIEW</div><div class="h3 mb-0">{{ $dailyTaskInreviewCount }}</div></div></div>
                    <div class="col"><div class="p-3 rounded-3" style="background: rgba(25,135,84,0.1); border: 1px solid #198754;"><div class="fw-bold text-success">COMPLETE</div><div class="h3 mb-0">{{ $dailyTaskCompleteCount }}</div></div></div>
                    <div class="col"><div class="p-3 rounded-3" style="background: rgba(220,53,69,0.1); border: 1px solid #dc3545;"><div class="fw-bold text-danger">NOT COMPLETE</div><div class="h3 mb-0">{{ $dailyTaskNotComplateCount }}</div></div></div>
                </div>
                
                <div class="row g-3 mt-2">
                     <div class="col-md-4"><div class="alert alert-danger mb-0 text-center"><i class="fas fa-exclamation-triangle me-1"></i> Overdue: <strong>{{ $dailyTaskCountOverdue }}</strong></div></div>
                     <div class="col-md-4"><div class="alert alert-primary mb-0 text-center"><i class="fas fa-calendar-day me-1"></i> Hari Ini: <strong>{{ $dailyTaskCountToday }}</strong></div></div>
                     <div class="col-md-4"><div class="alert alert-success mb-0 text-center"><i class="fas fa-calendar-alt me-1"></i> Mendatang: <strong>{{ $dailyTaskCountUpcoming }}</strong></div></div>
                </div>
            </div>
        </div>
        @endcanAccess

        @canAccess('softwareSharing','homes')
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="fas fa-box-open text-info me-2"></i> Software Center</h5>
                @canAccess('index','customer_software')
                <a href="{{ route('customer-software.index') }}" class="btn btn-sm btn-outline-info rounded-pill"><i class="fas fa-shopping-cart me-1"></i> Catalog</a>
                @endcanAccess
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6"><div class="p-3 rounded-3 text-center" style="background: #e8f5e9; color: #2e7d32;"><i class="fas fa-check-circle fs-4 mb-2"></i><div class="small fw-bold">ACTIVE</div><h4 id="stat-active" class="fw-bold mb-0"><i class="fas fa-spinner fa-spin"></i></h4></div></div>
                    <div class="col-md-3 col-6"><div class="p-3 rounded-3 text-center" style="background: #fff3e0; color: #ef6c00;"><i class="fas fa-exclamation-triangle fs-4 mb-2"></i><div class="small fw-bold">EXPIRING (7d)</div><h4 id="stat-expiring" class="fw-bold mb-0"><i class="fas fa-spinner fa-spin"></i></h4></div></div>
                    <div class="col-md-3 col-6"><div class="p-3 rounded-3 text-center" style="background: #ffebee; color: #c62828;"><i class="fas fa-times-circle fs-4 mb-2"></i><div class="small fw-bold">EXPIRED</div><h4 id="stat-expired" class="fw-bold mb-0"><i class="fas fa-spinner fa-spin"></i></h4></div></div>
                    <div class="col-md-3 col-6"><div class="p-3 rounded-3 text-center" style="background: #e3f2fd; color: #1565c0;"><i class="fas fa-box fs-4 mb-2"></i><div class="small fw-bold">CATALOG</div><h4 id="stat-softwares" class="fw-bold mb-0"><i class="fas fa-spinner fa-spin"></i></h4></div></div>
                </div>

                <div class="alert alert-warning" id="expiring-alert" style="display: none; border-radius: 10px;">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong id="expiring-count">0</strong> subscription(s) will expire soon. 
                    @canAccess('index','customer_subscriptions')<a href="{{ route('customer-subscription.index') }}" class="btn btn-sm btn-warning ms-2 rounded-pill">View & Renew</a>@endcanAccess
                </div>

                <div class="row g-4">
                    <div class="col-md-8">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-list me-1"></i> My Active Subscriptions</h6>
                        <div id="active-subscriptions-container" class="border rounded-3 bg-light"><div class="text-center py-4"><i class="fas fa-spinner fa-spin text-muted"></i></div></div>
                        
                        <div id="expired-card" style="display: none;" class="mt-4">
                            <h6 class="fw-bold text-danger"><i class="fas fa-history me-1"></i> Recently Expired</h6>
                            <div id="expired-subscriptions-container" class="border rounded-3 border-danger bg-white"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded-3 text-center" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); border: 1px dashed #ccc;">
                            <i class="fas fa-bolt fs-1 text-warning mb-3"></i>
                            <h5 class="fw-bold">Quick Access</h5>
                            <p class="small text-muted">Manage your software easily from the catalog or subscription list.</p>
                            @canAccess('index','customer_subscriptions')
                            <a href="{{ route('customer-subscription.index') }}" class="btn btn-primary w-100 rounded-pill mb-2"><i class="fas fa-list"></i> Manage My Sub</a>
                            @endcanAccess
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcanAccess

        @canAccess('showScheduleOb','homes')
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom pt-4 px-4">
                <h5 class="fw-bold mb-0"><i class="far fa-calendar-alt text-primary me-2"></i> Jadwal OB</h5>
            </div>
            <div class="card-body p-4">
                <div id="calendar" class="rounded-3 border p-2 bg-light"></div>
            </div>
        </div>
        @endcanAccess

        @if(Auth::user()->role->name == \App\Schemas\RoleSchema::BM)
        <div class="card border-0 shadow-sm mb-4 border-left-danger" style="border-radius: 14px; border-left: 5px solid #dc3545 !important;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold text-danger mb-0"><i class="fas fa-boxes me-2"></i> Peringatan Stok Habis (BM)</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive rounded border">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-dark"><tr><th>Nama Perlengkapan</th><th>Kode</th><th>Stok</th></tr></thead>
                        <tbody>
                            @forelse ($equipments as $equipment)
                            <tr><td>{{ $equipment->name }}</td><td><span class="badge bg-secondary">{{ $equipment->code }}</span></td><td class="text-danger fw-bold">{{ $equipment->total_stock }}</td></tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted"><i class="fas fa-check-circle text-success me-1"></i> Stok aman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@canAccess('store','office_media')
{{-- Modal Upload Moment --}}
<div class="modal fade" id="uploadMomentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow" action="{{ route('office-media.store') }}" method="POST" enctype="multipart/form-data" style="border-radius: 15px;">
            <div class="modal-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-upload text-danger me-2"></i> Upload Moment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <input type="hidden" name="type" value="image">
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Caption</label>
                    <input type="text" class="form-control" id="moment_caption" placeholder="Kisah seru hari ini...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Foto</label>
                    <input class="form-control" type="file" id="moment_photo" accept="image/*">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" class="btn btn-danger rounded-pill px-4">Upload Sekarang</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Youtube Embed --}}
<div class="modal fade" id="youtubeEmbedModal" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow" enctype="multipart/form-data" style="border-radius: 15px;">
            <div class="modal-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fab fa-youtube text-danger me-2"></i> Embed YouTube</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">YouTube URL</label>
                    <input type="url" class="form-control" id="youtube_url" placeholder="https://youtube.com/embed/...">
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" value="1" id="is_temporary" name="is_temporary" checked>
                        <label class="form-check-label ms-2" for="is_temporary">Temporary Link</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Caption</label>
                    <input type="text" class="form-control" id="video_caption" placeholder="Deskripsi video...">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" class="btn btn-danger rounded-pill px-4">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endcanAccess

@canAccess('index','office_media')
{{-- Modal Fullscreen Bootstrap Carousel --}}
<div class="modal fade" id="officeMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
      <div class="modal-header border-0 pb-0">
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 d-flex align-items-center">
        <div id="officeMediaCarousel" class="carousel slide w-100">
            <div class="carousel-inner" id="officeMediaCarouselInner"></div>
            <button class="carousel-control-prev" type="button" data-bs-target="#officeMediaCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#officeMediaCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
      </div>
    </div>
  </div>
</div>
@endcanAccess
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.2/main.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
/* LAPORAN PENGADUAN BANNER */
.laporan-banner {
    background: linear-gradient(135deg, #2d1b3d, #1a1a2e);
    border: 1px solid rgba(240,147,251,.35);
    border-radius: 14px;
    color: #e0e0ff;
    transition: all .2s;
}
.laporan-banner:hover {
    background: linear-gradient(135deg, #3d2450, #1e1e38);
    border-color: rgba(240,147,251,.6);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(240,147,251,.2);
}

/* GELAR badge item */
.gelar-item {
    position: relative;
    cursor: default;
    text-align: center;
    flex-shrink: 0;
}
.gelar-img-wrap {
    width: 90px;
    height: 90px;
    transition: transform .25s ease;
}
.gelar-item:hover .gelar-img-wrap {
    transform: scale(1.12) translateY(-3px);
}
.gelar-name {
    font-size: .68rem;
    color: #a0a8d0;
    margin-top: 6px;
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.gelar-count {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #f5a623;
    color: #1a1a2e;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: .65rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    box-shadow: 0 2px 6px rgba(0,0,0,.3);
}
/* CHALLENGE home cards */
.challenge-home-card { transition: transform .25s, box-shadow .25s; }
.challenge-home-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.3) !important; }
/* EVENT home cards */
.event-home-card { transition: transform .25s, box-shadow .25s; }
.event-home-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(102,126,234,.25) !important; }

/* Legacy badge-icon-wrap (jika masih dipakai) */
.badge-icon-wrap { transition: transform .2s, box-shadow .2s; }
.badge-icon-wrap:hover { transform: scale(1.18); box-shadow: 0 4px 16px rgba(240,147,251,.45); }

/* GAME UI GLOBALS */
.gamified-stat-card {
    position: relative;
    border-radius: 16px !important;
    overflow: hidden;
}
.gamified-stat-card .top-glow {
    position: absolute;
    top: 0; left: 0; right: 0; height: 4px;
}
.gamified-stat-card .icon-box {
    width: 48px; height: 48px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.gamified-light-card {
    background: #ffffff;
}

/* HOVER EFFECTS */
.hover-effect { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.hover-effect:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
.hover-card { transition: all 0.3s ease; }
.hover-card:hover { transform: translateY(-3px); background-color: #f8f9fa !important; border-color: #ddd !important; }

/* LEADERBOARD LISTS */
.custom-dark-list .list-group-item {
    background: transparent !important;
    border-color: rgba(255,255,255,.05) !important;
    color: #285cc3;
    padding: 10px 12px;
}
#leaderboard-list.dark-list .list-group-item {
    background: transparent !important;
    border-color: rgba(255,255,255,.08) !important;
    color: #285cc3;
}
.xp-leaderboard-card { border-radius: 14px !important; overflow: hidden; background:linear-gradient(145deg,#1a1a2e,#16213e); }
.xp-header-gradient { background: transparent !important; color: #e0e0ff !important; border-bottom: 1px solid rgba(102,126,234,.3) !important; }
.xp-rank-item {
    display: flex; align-items: center; padding: 8px 10px; border-radius: 12px; margin-bottom: 6px;
    background: rgba(255,255,255,.03); transition: background .2s;
}
.xp-rank-item:hover { background: rgba(102,126,234,.12); }
.xp-rank-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #f093fb); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .85rem; flex-shrink: 0; }
.xp-rank-num { font-size: .85rem; font-weight: 700; width: 24px; text-align: center; flex-shrink: 0; }

/* UTILITIES */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
.nav-pills .nav-link.active { background-color: #0d6efd; box-shadow: 0 2px 5px rgba(13,110,253,0.3); }
.nav-pills .nav-link { color: #6c757d; font-weight: 600; }
.d-none {
    display: none !important;
}
</style>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.2/main.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> 

@canAccess('join','meetings')
<script>
    function joinMeeting(userId, meetingId) 
    {   
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("meeting.join") }}',
            method: 'POST',
            data: {
                meeting_id: meetingId,
                user_id: userId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: response.message || 'Berhasil hadir.',
                        icon: 'success'
                    }).then(() => {
                        if (response.redirect_url) {
                            setTimeout(() => 
                            {   
                                loadMeetings();
                                var win = window.open(response.redirect_url, '_blank');
                                win.focus();
                            }, 1000);
                        } else {
                            setTimeout(() => location.reload(), 1000);
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: response.message || 'Gagal mencatat kehadiran.',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Terjadi Kesalahan',
                    text: xhr.responseJSON.message || 'Terjadi kesalahan.',
                    icon: 'error'
                });
            }
        }).always(() => {
            Swal.close();
        });
    }
</script>
@endcanAccess

@canAccess('meetingAgenda','homes')
<script>
    const currentUserId = "{{ auth()->id() }}";

    document.addEventListener('DOMContentLoaded', () => {
        loadMeetings('today');

        document.getElementById('today-tab').addEventListener('click', function () {
            switchTab(this);
            loadMeetings('today');
        });

        document.getElementById('week-tab').addEventListener('click', function () {
            switchTab(this);
            loadMeetings('week');
        });

        function switchTab(activeTab) {
            document.querySelectorAll('#agendaTabs .nav-link').forEach(tab => {
                tab.classList.remove('active');
            });
            activeTab.classList.add('active');
        }
    });

    async function loadMeetings(scope = 'today') {
        const tableBody = document.querySelector('#agenda-table tbody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`{{ route('home.meetingAgenda') }}?scope=${scope}`);
            const data = await res.json();
            tableBody.innerHTML = '';

            if (!data.length) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada agenda.</td></tr>';
                return;
            }

            for (const item of data) {
                const userIsAttending = item.participants?.some(p => p.id === currentUserId && p.pivot.is_attended);
                                
                const locationOrAction = item.meeting_type !== 'offline' ? (userIsAttending ? `<span class="badge bg-success">Hadir</span>`: (item.google_meet_link ? (item.is_already ? `<button class="btn btn-sm btn-success" onclick="joinMeeting('${currentUserId}', '${item.id}')"><i class="fas fa-sign-in-alt"></i> Bergabung</button>` : `<span class="badge bg-warning text-dark mt-1">Segera Dimulai</span>` ) : '-')) : (item.meeting_location || '-');

                const url = `{{ route('meeting.show', ':id') }}`.replace(':id', item.slug);

                const row = `
                    <tr>
                        <td class="fw-bold">
                            <a href="${url}" data-id="${item.id}" class="text-decoration-none">
                                ${item.meeting_name}
                            </a>
                        </td>
                        <td>${formatDate(item.start_date)}</td>
                        <td>${item.start_time} - ${item.end_time}</td>
                        <td>${item.meeting_type_badge}</td>
                        <td class="text-center">${locationOrAction}</td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            }

        } catch (err) {
            console.error(err);
            tableBody.innerHTML = '<tr><td colspan="5" class="text-danger text-center py-4">Gagal memuat agenda.</td></tr>';
        }
    }

    function formatDate(dateStr) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('id-ID', options);
    }
</script>
@endcanAccess

@canAccess('infoPic','subscribe_letters')
<script>
    async function loadLetterReminderPIC() {
        const container = document.querySelector('#reminder-letter-pic');
        container.innerHTML = `<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat pengingat surat (PIC)...</div>`;
        try {
            const response = await fetch("{{ route('reminder.letter.pic') }}");
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            container.innerHTML = ``;
        }
    }
    document.addEventListener("DOMContentLoaded", () => { loadLetterReminderPIC(); });
</script>
@endcanAccess

@canAccess('infoManager','subscribe_letters')
<script>
    async function loadLetterReminderManager() {
        const container = document.querySelector('#reminder-letter-manager');
        container.innerHTML = `<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat pengingat surat (Manager)...</div>`;
        try {
            const response = await fetch("{{ route('reminder.letter.manager') }}");
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            container.innerHTML = ``;
        }
    }
    document.addEventListener("DOMContentLoaded", () => { loadLetterReminderManager(); });
</script>
@endcanAccess

@canAccess('infoManager','vehicles')
<script>
    async function loadVehicleReminderManager() {
        const container = document.querySelector('#vehicle-reminder-manager');
        container.innerHTML = `<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat pengingat kendaraan (Manager)...</div>`;
        try {
            const response = await fetch("{{ route('reminder.vehicle.manager') }}");
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            container.innerHTML = ``;
        }
    }
    document.addEventListener("DOMContentLoaded", () => { loadVehicleReminderManager(); });
</script>
@endcanAccess

@canAccess('infoPic','vehicles')
<script>
    async function loadVehicleReminderPIC() {
        const container = document.querySelector('#vehicle-reminder-pic');
        container.innerHTML = `<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat pengingat kendaraan (PIC)...</div>`;
        try {
            const response = await fetch("{{ route('reminder.vehicle.pic') }}");
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            container.innerHTML = ``;
        }
    }
    document.addEventListener("DOMContentLoaded", () => { loadVehicleReminderPIC(); });
</script>
@endcanAccess

@canAccess('infoPhotoReminderPic','vehicles')
<script>
    async function loadVehiclePhotoReminderPIC() {
        const container = document.querySelector('#vehicle-photo-reminder-pic');
        try {
            const response = await fetch("{{ route('reminder.vehicle.photo.pic') }}");
            const data = await response.json();

            if (!data.vehicles || data.vehicles.length === 0) {
                container.innerHTML = '';
                return;
            }

            const items = data.vehicles.map(v => `
                <li>
                    Kendaraan <strong>${v.vehicle_id} ${v.vehicle_type}</strong>
                    belum terdapat foto di bulan <strong>${data.bulan} ${data.tahun}</strong>, segera melakukan foto.
                    <a href="${v.show_url}" class="ms-1 small"><i class="fas fa-arrow-right"></i> Lihat</a>
                </li>
            `).join('');

            container.innerHTML = `
                <div class="alert alert-warning mb-2" style="border-left: 4px solid #ffc107;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-camera mt-1" style="font-size:1.1rem;color:#856404;flex-shrink:0;"></i>
                        <div>
                            <strong>Pengingat Kendaraan Belum Terdapat Foto — ${data.bulan} ${data.tahun}</strong>
                            <ul class="mb-0 mt-1 ps-3">${items}</ul>
                        </div>
                    </div>
                </div>
            `;
        } catch (error) {
            container.innerHTML = '';
        }
    }
    document.addEventListener("DOMContentLoaded", () => { loadVehiclePhotoReminderPIC(); });
</script>
@endcanAccess

@canAccess('listDayoff','homes')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.getElementById('cuti-today-container');
        try {
            const response = await fetch("{{ route('home.listDayoff') }}");
            const data = await response.json();
            container.innerHTML = data.html;
        } catch (error) {
            container.innerHTML = `<div class="text-danger small"><i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat data.</div>`;
        }
    });
</script>
@endcanAccess

@canAccess('reminderDashboard', 'weekly_reports')
<script>
    $(document).ready(function () {
        $.ajax({
            url: "{{ route('weekly-report.reminderDashboard') }}",
            method: "GET",
            success: function (res) { $('#weekly-report-reminder').html(res.html); },
            error: function () { $('#weekly-report-reminder').hide(); }
        });
    });
</script>
@endcanAccess

@canAccess('infoApprovementHr', 'dayoffs')
<script>
    $(document).ready(async function () {
        const response = await $.get("{{ route('dayoff.infoApprovementHr') }}");
        $('#count-hr').text(response.total);
    });
</script>
@endcanAccess

@canAccess('infoApprovementFinance', 'dayoffs')
<script>
    $(document).ready(async function () {
        const response = await $.get("{{ route('dayoff.infoApprovementFinance') }}");
        $('#count-finance').text(response.total);
    });
</script>
@endcanAccess

@canAccess('index','office_media')
<script>
        $(document).ready(function () { loadOfficeMedia(); });
        function loadOfficeMedia() 
        {
            $('#office-media-image-section, #office-media-youtube-section').html(`
                <div class="d-flex justify-content-center h-100 align-items-center">
                    <div class="spinner-border text-secondary" role="status"></div>
                </div>
            `);

            $.ajax({
                url: "{{ route('office-media.index') }}",
                type: "GET",
                success: function(res) {
                    if (res.status === 'success') {                  
                        $('#office-media-image-section').html(res.data.image);
                        $('#office-media-youtube-section').html(res.data.youtube);
                    }
                },
                error: function() {
                    $('#office-media-image-section, #office-media-youtube-section').html(`<div class="text-center text-danger">Failed to load content.</div>`);
                }
            });
        }
</script>
<script>
    async function handleMediaClick(clickedIndex) {
        const $allImages = $('.office-media-thumb');
        let slidesHtml = '';

        $allImages.each(function (index) {
            const imgUrl = $(this).data('url');
            const title = $(this).attr('alt') ?? '';
            const activeClass = (index === clickedIndex) ? 'active' : '';

            slidesHtml += `
                <div class="carousel-item ${activeClass}">
                    <div class="d-flex justify-content-center align-items-center" style="height:80vh;">
                        <img src="${imgUrl}" class="d-block mx-auto img-fluid rounded" alt="${title}" style="max-height:100%; object-fit: contain;">
                    </div>
                </div>
                `;
        });

        $('#officeMediaCarouselInner').html(slidesHtml);
        await new Promise(resolve => setTimeout(resolve, 10));

        const carouselElement = document.getElementById('officeMediaCarousel');
        if (carouselElement.carouselInstance) {
            carouselElement.carouselInstance.dispose();
        }

        carouselElement.carouselInstance = new bootstrap.Carousel(carouselElement, {
            interval: false, ride: false, keyboard: true,
        });

        carouselElement.carouselInstance.to(clickedIndex);

        const modalElement = document.getElementById('officeMediaModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }

    $(document).on('click', '.office-media-thumb', function () {
        const clickedIndex = $(this).data('index');
        handleMediaClick(clickedIndex);
    });
</script>
@endcanAccess

@canAccess('store','office_media')
<script>
    $(document).ready(function () {

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('#uploadMomentModal form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append('type', 'image');
            formData.append('title', $('#moment_caption').val());
            formData.append('file', $('#moment_photo')[0].files[0]);
            
            $.ajax({
                url: "{{ route('office-media.store') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('#uploadMomentModal button[type="submit"]').prop('disabled', true).text('Uploading...');
                },
                success: function (response) {
                    Swal.fire({
                        title: 'Success!', text: 'Media berhasil diupload.', icon: 'success',
                        timer: 2000, timerProgressBar: true, showConfirmButton: false,
                        willClose: () => {
                            loadOfficeMedia();
                            $('#uploadMomentModal form')[0].reset();
                            $('#uploadMomentModal .close').click();
                        }
                    });
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Gagal upload foto');
                },
                complete: function () {
                    $('#uploadMomentModal button[type="submit"]').prop('disabled', false).text('Upload Sekarang');
                }
            });
        });

        $('#youtubeEmbedModal form').on('submit', function (e) {
            e.preventDefault();
            let youtubeUrl = $('#youtube_url').val();
            let caption = $('#video_caption').val();
            let isTemporary = $('#is_temporary').is(':checked');

            $('#youtubeEmbedModal .close').click();

            $.ajax({
                url: "{{ route('office-media.store') }}",
                method: "POST",
                data: {
                    type: 'youtube', youtube_url: youtubeUrl, title: caption, is_temporary: isTemporary ? 1 : 0
                },
                success: function (response) {
                    Swal.fire({
                        title: 'Success', text: 'Video berhasil disimpan', icon: 'success',
                        timer: 2000, timerProgressBar: true, showConfirmButton: false,
                        willClose: () => { loadOfficeMedia(); }
                    });
                    $('#youtubeEmbedModal form')[0].reset();
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Gagal menyimpan video');
                }
            });
        });
    });
</script>
@endcanAccess

@canAccess('destroy','office_media')
<script>
    $(document).on('click', '.delete-media-btn', function () {
        const mediaId = $(this).data('id');
        if (!confirm('Yakin ingin menghapus media ini?')) return;
    
        $.ajax({
            url: `{{ route('office-media.destroy', ':id') }}`.replace(':id', mediaId),
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                if (res.status === 'success') { loadOfficeMedia(); }
            },
            error: function () { alert('Gagal menghapus media.'); }
        });
    });
</script>
@endcanAccess

@canAccess('overdueRanking','homes')
<script>
    async function loadOverdueLeaderboard()
    {
        try {
            $('#overdue-loader, #overdue-inreview-loader').removeClass('d-none');
            $('#overdue-ranking, #overdue-inreview-ranking').addClass('d-none');

            const response = await $.get('{{ route("home.overdueRanking") }}');

            const container = $('#overdue-ranking');
            const containerInreview = $('#overdue-inreview-ranking');

            container.empty();
            containerInreview.empty();

            if (response.status === 'success') {
                if(response.data && response.data.overdueUsers.length > 0) {
                    response.data.overdueUsers.forEach((user, index) => {
                        container.append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    ${index + 1 <= 9 ? `<i class="bi bi-${index + 1}-circle-fill text-danger me-2"></i>` : `<span class="badge bg-danger text-dark me-2">${index + 1}</span>`}
                                    ${user.name}
                                </span>
                                <span class="badge bg-danger">${user.overdue_count}</span>
                            </li>
                        `);
                    });
                } else {
                    container.append(`<li class="list-group-item text-center text-muted border-0">No data available</li>`);
                }

                if(response.data && response.data.overdueInReviewUsers.length > 0) {
                    response.data.overdueInReviewUsers.forEach((user, index) => {
                        containerInreview.append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    ${index + 1 <= 9 ? `<i class="bi bi-${index + 1}-circle-fill text-warning me-2"></i>` : `<span class="badge bg-warning text-dark me-2">${index + 1}</span>`}
                                    ${user.name}
                                </span>
                                <span class="badge bg-warning text-dark">${user.overdue_count}</span>
                            </li>
                        `);
                    });
                } else {
                    containerInreview.append(`<li class="list-group-item text-center text-muted border-0">No data available</li>`);
                }
            } else {
                container.append(`<li class="list-group-item text-center text-muted border-0">No data available</li>`);
                containerInreview.append(`<li class="list-group-item text-center text-muted border-0">No data available</li>`);
            }
            
            $('#overdue-loader, #overdue-inreview-loader').addClass('d-none');
            container.removeClass('d-none');
            containerInreview.removeClass('d-none');
        } catch (error) {
            $('#overdue-loader').html('<p class="text-danger small text-center">Gagal memuat</p>');
            $('#overdue-inreview-loader').html('<p class="text-danger small text-center">Gagal memuat</p>');
        }
    }

    $(document).ready(function () {
        loadOverdueLeaderboard();
    });
</script>
@endcanAccess

@canAccess('leaderboard','homes')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchLeaderboard();
    });

    async function fetchLeaderboard() {
        try {
            const response = await $.ajax({
                url: '{{ route("home.leaderboard") }}',
                method: 'GET',
            });

            const leaderboard = response.data;
            const list = $('#leaderboard-list');
            list.empty();
            list.addClass('dark-list');

            if (leaderboard.length === 0) {
                list.append(`<li class="list-group-item text-center text-muted border-0">No data available</li>`);
            } else {
                leaderboard.forEach((item, index) => {
                    const rankEmoji = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `<span style="color:#a0a8d0;font-size:.75rem;display:inline-block;width:20px;text-align:center;">${index+1}</span>`;
                    const html = `
                        <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-1" style="font-size:.85rem;">
                            <span>${rankEmoji} <span style="color:#285cc3;" class="ms-1">${item.name}</span></span>
                            <span class="badge" style="background:rgba(245,166,35,.2);color:#f5a623;border:1px solid rgba(245,166,35,.3);">${item.currentScore}</span>
                        </li>
                    `;
                    list.append(html);
                });
            }

            $('#leaderboard-loader').addClass('d-none');
            $('#leaderboard-list').removeClass('d-none');
        } catch (error) {
            $('#leaderboard-loader').html('<p class="text-danger small text-center">Failed to load</p>');
        }
    }
</script>
@endcanAccess

@canAccess('userBadges','homes')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadUserBadges();
    });

    async function loadUserBadges() {
        const container = document.getElementById('gelar-container');
        const loader    = document.getElementById('gelar-loader');
        const empty     = document.getElementById('gelar-empty');
        if (!container) return;
        try {
            const res  = await fetch('{{ route("home.userBadges") }}');
            const json = await res.json();
            const data = json.data;

            loader.classList.add('d-none');

            if (!data || data.length === 0) {
                empty.classList.remove('d-none');
                empty.classList.add('d-flex');
                return;
            }

            let html = '';
            data.forEach(b => {
                const imgHtml = b.image_url
                    ? `<img src="${b.image_url}"
                            style="width:90px;height:auto;object-fit:contain;filter:drop-shadow(0 4px 14px rgba(240,147,251,.5));"
                            alt="${b.name}">`
                    : `<span style="font-size:3.2rem;filter:drop-shadow(0 4px 10px rgba(240,147,251,.4));">🏅</span>`;

                html += `
                <div class="gelar-item"
                     data-bs-toggle="tooltip" data-bs-placement="top"
                     title="${b.name}${b.count > 1 ? ' (×' + b.count + ')' : ''} — ${b.received_at}">
                    <div class="gelar-img-wrap d-flex align-items-center justify-content-center">
                        ${imgHtml}
                    </div>
                    ${b.count > 1 ? `<span class="gelar-count">${b.count}</span>` : ''}
                    <div class="gelar-name mt-3">${b.name}</div>
                </div>`;
            });

            container.innerHTML = html;
            container.style.display = 'flex';
            container.classList.remove('d-none');

            // Reinit tooltips
            container.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        } catch (e) {
            if (loader) loader.innerHTML = '<small style="color:#f87171;">Gagal memuat gelar</small>';
        }
    }
</script>
@endcanAccess

@canAccess('activeChallenges','homes')
<script>
document.addEventListener('DOMContentLoaded', function () { loadActiveChallenges(); });

async function loadActiveChallenges() {
    const section   = document.getElementById('challenge-section');
    const loader    = document.getElementById('challenge-loader');
    const container = document.getElementById('challenge-container');
    if (!section) return;
    try {
        const res  = await fetch('{{ route("home.activeChallenges") }}');
        const json = await res.json();
        const data = json.data;

        if (!data || data.length === 0) {
            // Kosong — sembunyikan seluruh section, tidak ganggu elemen lain
            return;
        }

        loader.classList.add('d-none');
        section.style.display = 'block';

        let html = '<div class="list-group gap-2">';
        data.forEach(c => {
            const isComplete = c.percent >= 100;
            const barColor   = isComplete ? '#38ef7d' : c.module_color;
            const rewardDone = c.reward_given
                ? `<span class="badge ms-1" style="background:rgba(56,239,125,.2);color:#38ef7d;font-size:.62rem;border:1px solid rgba(56,239,125,.3);"><i class="fas fa-check me-1"></i>Reward ✓</span>`
                : '';
            const draftIcon = c.status === 'draft' ? `<span class="badge ms-1" style="background:rgba(255,255,255,.1);color:#a0a8d0;font-size:.62rem;border:1px solid rgba(255,255,255,.2);"><i class="fas fa-pencil-alt me-1"></i>Draft</span>` : '';

            html += `
            <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center challenge-home-card" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border:none;border-left:4px solid ${c.module_color};border-radius:12px;padding:16px;">
                <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                    <div style="width:46px;height:46px;background:rgba(255,255,255,.07);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="${c.module_icon}" style="color:${c.module_color};font-size:1.3rem;"></i>
                    </div>
                    <div>
                        <div style="color:#e0e0ff;font-weight:700;font-size:.95rem;margin-bottom:2px;">
                            ${c.name}${draftIcon}${rewardDone}
                        </div>
                        <div style="color:#a0a8d0;font-size:.75rem;">
                            ${c.module_label} &bull; ${isComplete ? '<span style="color:#38ef7d">Selesai!</span>' : `<span style="color:#f5a623">${c.days_remaining} hari lagi</span>`}
                            ${c.reward_point > 0 ? ` &bull; <span style="color:#f5a623"><i class="fas fa-coins"></i> +${c.reward_point} Pts</span>` : ''}
                            ${c.reward_xp > 0 ? ` &bull; <span style="color:#f093fb"><i class="fas fa-star"></i> +${c.reward_xp} XP</span>` : ''}
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column" style="min-width:200px;">
                    <div class="mb-1 d-flex justify-content-between">
                        <small style="color:#a0a8d0;font-size:.7rem;">Progress</small>
                        <small style="color:${barColor};font-weight:700;font-size:.75rem;">${c.current.toLocaleString('id-ID')} / ${c.target.toLocaleString('id-ID')} ${c.unit} (${c.percent}%)</small>
                    </div>
                    <div style="height:8px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:${c.percent}%;background:linear-gradient(90deg,${barColor},${c.module_color});border-radius:4px;transition:width 1s ease;box-shadow:0 0 8px ${barColor}55;"></div>
                    </div>
                </div>
            </div>`;
        });
        html += '</div>';

        container.innerHTML = html;
        container.classList.remove('d-none');
    } catch (e) {
        if (loader) loader.innerHTML = '<small style="color:#f87171;">Gagal memuat challenge.</small>';
    }
}
</script>
@endcanAccess

@canAccess('activeEvents','homes')
<script>
let _eventWeekOffset = 0;

document.addEventListener('DOMContentLoaded', function () { loadActiveEvents(0); });

function shiftWeek(delta) {
    _eventWeekOffset += delta;
    loadActiveEvents(_eventWeekOffset);
}

async function loadActiveEvents(weekOffset) {
    const section   = document.getElementById('event-section');
    const loader    = document.getElementById('event-loader');
    const calWrap   = document.getElementById('event-calendar-wrap');
    const emptyDiv  = document.getElementById('event-empty');
    const dayHeader = document.getElementById('event-day-header');
    const rowsEl    = document.getElementById('event-rows');
    const weekLabel = document.getElementById('event-week-label');
    if (!section) return;

    // Tampilkan loader, sembunyikan calendar
    loader.classList.remove('d-none');
    calWrap.style.display  = 'none';
    emptyDiv.style.display = 'none';

    try {
        const res  = await fetch(`{{ route("home.activeEvents") }}?week_offset=${weekOffset}`);
        const json = await res.json();
        const d    = json.data;

        section.style.display = 'block';
        loader.classList.add('d-none');

        if (weekLabel) weekLabel.textContent = d.week_label;

        // ── Header hari ────────────────────────────────────────
        dayHeader.innerHTML = d.days.map((day, i) => `
            <div style="
                text-align:center;padding:10px 4px;
                border-right:${i < 6 ? '1px solid rgba(102,126,234,.1)' : 'none'};
                background:${day.is_today ? 'rgba(102,126,234,.12)' : 'transparent'};
                position:relative;">
                ${day.is_today ? '<div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#667eea,#f093fb);"></div>' : ''}
                <div style="color:${day.is_today ? '#a5b4fc' : '#4b5563'};font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;">${day.label}</div>
                <div style="
                    color:${day.is_today ? '#fff' : '#6b7280'};
                    font-size:.85rem;font-weight:${day.is_today ? '700' : '400'};
                    ${day.is_today ? 'background:rgba(102,126,234,.4);border-radius:50%;width:24px;height:24px;line-height:24px;margin:3px auto 0;box-shadow:0 0 8px rgba(102,126,234,.6);' : 'margin-top:3px;'}
                ">${day.day_number}</div>
            </div>`
        ).join('');

        // ── Rows event ──────────────────────────────────────────
        if (!d.rows || d.rows.length === 0) {
            rowsEl.innerHTML = '';
            calWrap.style.display = 'block';
            emptyDiv.style.display = 'block';
            return;
        }

        calWrap.style.display = 'block';

        // Posisi "hari ini" untuk vertical highlight
        const todayCol = d.days.findIndex(day => day.is_today);

        rowsEl.innerHTML = d.rows.map(row => {
            const hex  = row.color || '#667eea';
            const timeText    = row.time_range ? `<i class="fas fa-clock" style="font-size:.55rem;opacity:.7;margin:0 3px;"></i><span style="font-size:.62rem;opacity:.8;">${row.time_range}</span>` : '';
            const routineTag  = row.is_routine  ? `<i class="fas fa-sync-alt" style="font-size:.55rem;margin-left:5px;opacity:.6;" title="Rutin"></i>` : '';

            // Render 7 sel, sel dalam span dikosongkan
            const cells = Array.from({length: 7}, (_, i) => {
                const isStart  = i === row.col_start;
                const isInSpan = i > row.col_start && i < row.col_start + row.col_span;
                const isEmpty  = i < row.col_start || i >= row.col_start + row.col_span;

                if (isStart) {
                    return `<div style="grid-column:${i+1}/span ${row.col_span};">
                        <a href="${row.detail_url}" style="text-decoration:none;display:block;">
                            <div style="
                                background:linear-gradient(90deg,${hex}cc,${hex}77);
                                border-left:3px solid ${hex};
                                border-radius:0 6px 6px 0;
                                padding:6px 10px;
                                display:flex;align-items:center;gap:4px;
                                white-space:nowrap;overflow:hidden;
                                box-shadow:0 2px 12px ${hex}44,inset 0 0 0 1px ${hex}33;
                                transition:all .2s;
                                cursor:pointer;"
                                onmouseover="this.style.background='linear-gradient(90deg,${hex}ff,${hex}99)';this.style.boxShadow='0 4px 20px ${hex}66,inset 0 0 0 1px ${hex}55';"
                                onmouseout="this.style.background='linear-gradient(90deg,${hex}cc,${hex}77)';this.style.boxShadow='0 2px 12px ${hex}44,inset 0 0 0 1px ${hex}33';">
                                <span style="color:#fff;font-size:.72rem;font-weight:700;overflow:hidden;text-overflow:ellipsis;text-shadow:0 1px 4px rgba(0,0,0,.4);">${row.name}</span>
                                ${timeText}${routineTag}
                            </div>
                        </a>
                    </div>`;
                }
                if (isEmpty) return `<div></div>`;
                return ''; // isInSpan — tidak perlu elemen, sudah di-cover span
            }).join('');

            return `<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;">${cells}</div>`;
        }).join('');

        calWrap.style.display = 'block';

    } catch (e) {
        loader.innerHTML = '<small style="color:#f87171;">Gagal memuat event.</small>';
    }
}
</script>
@endcanAccess

@canAccess('xpLeaderboard','homes')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadXpTop5();
    });

    async function loadXpTop5() {
        const container = document.getElementById('xp-top5-container');
        if (!container) return;
        try {
            const res = await fetch('{{ route("home.xpLeaderboard") }}');
            const json = await res.json();
            const data = json.data;

            if (!data || data.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-3" style="font-size:.82rem;">Belum ada data XP.</div>';
                return;
            }

            let html = '';
            data.forEach((u, i) => {
                const rankLabel = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `<span style="color:#a0a8d0;font-weight:700;">${i+1}</span>`;
                html += `
                <div class="xp-rank-item">
                    <div class="xp-rank-num">${rankLabel}</div>
                    <div class="xp-rank-avatar mx-2">${u.initial}</div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div style="font-size:.82rem;color:#285cc3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${u.name}</div>
                    </div>
                    <div class="text-end ms-2" style="flex-shrink:0;">
                        <div style="font-size:.72rem;color:#f093fb;font-weight:700;">${u.total_xp.toLocaleString('id-ID')} XP</div>
                        <div style="font-size:.65rem;color:#a0a8d0;">${u.badge} ${u.level}</div>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<div class="text-danger text-center py-2" style="font-size:.8rem;">Gagal memuat data XP.</div>';
        }
    }
</script>
@endcanAccess

@canAccess('dashboardReport','homes')
<script>
    $(document).ready(async function() {
        try {
            const response = await $.ajax({
                url: "{{ route('home.dashboardReport') }}",
                type: "GET",
                dataType: "json"
            });

            if (response.status === "success") {
                $('#currentScore').text(response.data.currentScore);
                $('#totalPoints').text(response.data.totalPoints);
                $('#totalTasksComplete').text(response.data.totalTasksComplete);
                $('#checkin_point_percentage').text(response.data.checkin_point_percentage);
                $('#checkins').text(response.data.checkins ? "Sudah" : "Belum");
            }
        } catch (error) {
            console.error('Error fetching dashboard report:', error);
        }
    });

    (function() {
        const totalXp = {{ Auth::user()->total_xp ?? 0 }};
        @php
            $rawLevels = collect(\App\Helpers\XpHelper::levels()); // sudah terurut ascending dari helper
            $xpLevels  = $rawLevels->map(function ($l, $idx) use ($rawLevels) {
                $next = $rawLevels->get($idx + 1);
                return [
                    'label' => $l['label'],
                    'badge' => $l['badge'],
                    'min'   => $l['min'],
                    'max'   => $next ? $next['min'] - 1 : 9999999,
                ];
            })->values();
        @endphp
        const levels = {!! $xpLevels->toJson() !!};
        const lvl = levels.slice().reverse().find(l => totalXp >= l.min) || levels[0];
        const pct = Math.min(100, ((totalXp - lvl.min) / (lvl.max - lvl.min + 1)) * 100);

        document.getElementById('profile-xp-label').textContent = totalXp.toLocaleString('id-ID') + ' XP';
        document.getElementById('profile-level-badge').textContent = lvl.badge;
        document.getElementById('profile-level-badge').title = lvl.label;

        setTimeout(() => {
            const bar = document.getElementById('profile-xp-bar');
            if (bar) bar.style.width = pct + '%';
        }, 300);
    })();
</script>
@endcanAccess

<script>
    $(document).ready(function() {
        $('input[name="start_date"]').on('change', function() {
            var startDateValue = $(this).val();
            $('input[name="end_date"]').val(startDateValue);
        });
    });
</script>

@canAccess('showScheduleOb','homes')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: [
            @foreach($schedules as $schedule) {
                title: '{{ $schedule->user->name }} - {{ $schedule->shiftingOb->name }}',
                start: '{{ $schedule->date }}',
                description: `
                            <b>User:</b> {{ $schedule->user->name }}<br>
                            <b>Shift:</b> {{ $schedule->shiftingOb->name }}<br>
                            <b>Clock In:</b> {{ $schedule->shiftingOb->clock_in }}<br>
                            <b>Clock Out:</b> {{ $schedule->shiftingOb->clock_out }}<br>
                            <b>Real Clock In:</b> {{ $schedule->attendance ? $schedule->attendance->clock_in : '-' }}<br>
                            <b>Real Clock Out:</b> {{ $schedule->attendance ? $schedule->attendance->clock_out : '-' }}<br>
                        `,
                id: '{{ $schedule->id }}',
                extendedProps: {
                    user_id: '{{ $schedule->user_id }}',
                    shifting_ob_id: '{{ $schedule->shifting_ob_id }}'
                }
            },
            @endforeach
        ],
        eventDidMount: function(info) {
            new bootstrap.Tooltip(info.el, {
                title: info.event.extendedProps.description,
                html: true,
                container: 'body'
            });
        }
    });

    calendar.render();
});
</script>
@endcanAccess

@canAccess('softwareSharing','homes')
<script>
$(document).ready(function() { loadDashboardData(); });

async function loadDashboardData() {
    try {
        const response = await $.ajax({
            url: '{{ route('home.softwareSharing') }}',
            type: 'GET'
        });

        if (response.success) {
            updateStatistics(response.data.statistics);
            renderSubscriptions(response.data.subscriptions);
            renderExpired(response.data.recent_expired);
        }
    } catch (error) {
        console.error('Failed to load software sharing data');
    }
}

function updateStatistics(stats) {
    $('#stat-active').text(stats.active_subscriptions);
    $('#stat-expiring').text(stats.expiring_soon);
    $('#stat-expired').text(stats.expired_subscriptions);
    $('#stat-softwares').text(stats.total_softwares);

    if (stats.expiring_soon > 0) {
        $('#expiring-count').text(stats.expiring_soon);
        $('#expiring-alert').show();
    }
}

function renderSubscriptions(subscriptions) {
    const container = $('#active-subscriptions-container');
    if (subscriptions.length === 0) {
        container.html(`<div class="text-center py-5"><p class="text-muted">No active subscriptions yet.</p></div>`);
        return;
    }

    let html = `<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Software</th><th>Package</th><th>Expires</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead><tbody>`;

    subscriptions.forEach(sub => {
        const statusBadge = sub.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Expired</span>';
        const expiringBadge = sub.is_expiring_soon ? `<br><span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> ${sub.days_until_expiry} days</span>` : '';
        const renewButton = sub.is_expiring_soon ? `<a href="${sub.renew_url}" class="btn btn-sm btn-warning"><i class="fas fa-sync"></i></a>` : '';

        html += `
            <tr>
                <td class="fw-bold">${sub.software.nama}</td>
                <td><span class="badge bg-info">${sub.package.nama}</span></td>
                <td>${sub.tanggal_expired}${expiringBadge}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <a href="${sub.detail_url}" class="btn btn-info text-white"><i class="fas fa-eye"></i></a>
                        ${renewButton}
                    </div>
                </td>
            </tr>`;
    });

    html += `</tbody></table></div>`;
    container.html(html);
}

function renderExpired(expired) {
    if (expired.length === 0) return;
    $('#expired-card').show();
    const container = $('#expired-subscriptions-container');

    let html = `<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Software</th><th>Package</th><th>Expired</th><th class="text-center">Action</th></tr></thead><tbody>`;
    
    expired.forEach(sub => {
        html += `<tr><td>${sub.software.nama}</td><td>${sub.package.nama}</td><td>${sub.tanggal_expired}</td>
                 <td class="text-center"><a href="${sub.software_url}" class="btn btn-sm btn-outline-success"><i class="fas fa-redo"></i> Renew</a></td></tr>`;
    });
    
    html += `</tbody></table></div>`;
    container.html(html);
}
</script>
@endcanAccess
@stop