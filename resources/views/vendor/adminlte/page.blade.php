@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@section('adminlte_css')
    @livewireStyles
    @stack('css')
    @yield('css')
    <style>
    /* ═══════════════════════════════════════════════════════════════
       GLOBAL SOFT LAYOUT OVERRIDES
       ═══════════════════════════════════════════════════════════════ */

    /* ── Base ───────────────────────────────────────────────────── */
    body, .wrapper { background: #f0f2f8 !important; }
    * { -webkit-font-smoothing: antialiased; }

    /* ── Navbar ─────────────────────────────────────────────────── */
    .main-header.navbar {
        background: #ffffff !important;
        border-bottom: 1px solid #e8ecf4 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,.06) !important;
        padding: 0 16px !important;
        min-height: 58px !important;
    }
    .main-header .navbar-nav .nav-link {
        color: #4a5568 !important;
        padding: 0 10px !important;
        height: 58px !important;
        display: flex !important;
        align-items: center !important;
        font-size: .875rem !important;
    }
    .main-header .navbar-nav .nav-link:hover { color: #2563eb !important; }
    .main-header [data-widget="pushmenu"] {
        color: #64748b !important;
        font-size: 1.1rem !important;
    }
    /* Company label */
    .nav-brand-label {
        font-size: .82rem; font-weight: 600;
        color: #334155; letter-spacing: .1px;
    }
    /* Notification icon */
    .nav-icon-btn {
        position: relative;
    }
    .nav-icon-btn .badge-dot {
        position: absolute; top: 12px; right: 6px;
        width: 7px; height: 7px;
        background: #ef4444; border-radius: 50%;
        border: 1.5px solid #fff;
        padding: 0 !important;
    }
    .nav-link-email {
        white-space: normal; display: block; word-wrap: break-word;
        font-size: .8rem !important; color: #334155 !important;
    }

    /* User dropdown toggle */
    .user-menu > .nav-link {
        height: 58px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px;
        color: #334155 !important;
        font-size: .83rem !important;
        font-weight: 600 !important;
    }
    .user-menu .dropdown-menu {
        border: none !important;
        box-shadow: 0 8px 30px rgba(0,0,0,.12) !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        margin-top: 8px !important;
    }
    .user-menu .user-header {
        background: linear-gradient(135deg, #1a3a5c, #2563a8) !important;
        padding: 16px !important;
        border-radius: 0 !important;
    }
    .user-menu .user-footer {
        background: #f8fafc !important;
        border-top: 1px solid #e8ecf4 !important;
        padding: 10px 12px !important;
    }
    .user-menu .user-footer .btn {
        border-radius: 8px !important;
        font-size: .78rem !important;
    }

    /* ── Sidebar ────────────────────────────────────────────────── */
    .main-sidebar.sidebar-custom {
        background: linear-gradient(180deg, #0f2544 0%, #1a3a5c 60%, #1e4976 100%) !important;
        border-right: none !important;
        box-shadow: 4px 0 20px rgba(0,0,0,.15) !important;
        width: 250px !important;
    }

    /* Brand area */
    .main-sidebar .brand-link {
        background: rgba(255,255,255,.06) !important;
        border-bottom: 1px solid rgba(255,255,255,.08) !important;
        padding: 14px 16px !important;
        display: flex !important;
        align-items: center !important;
    }
    .main-sidebar .brand-link:hover {
        background: rgba(255,255,255,.1) !important;
    }
    .main-sidebar .brand-text {
        color: #ffffff !important;
        font-size: .95rem !important;
        font-weight: 700 !important;
        letter-spacing: .2px !important;
    }

    /* User panel */
    .sidebar-user-panel {
        display: flex; align-items: center; gap: 12px;
        padding: 16px 18px 12px;
        border-bottom: 1px solid rgba(255,255,255,.08);
        margin-bottom: 6px;
    }
    .sidebar-user-avatar {
        width: 38px; height: 38px; border-radius: 10px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff; font-size: .9rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .sidebar-user-name {
        font-size: .82rem; font-weight: 700; color: #f1f5f9;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sidebar-user-role {
        font-size: .7rem; color: rgba(255,255,255,.5); margin-top: 2px;
    }

    /* Sidebar inner wrapper */
    .sidebar-custom-inner {
        padding-top: 0 !important;
    }

    /* Nav headers */
    .main-sidebar .nav-header {
        color: rgba(255,255,255,.35) !important;
        font-size: .62rem !important;
        letter-spacing: 1.4px !important;
        text-transform: uppercase !important;
        padding: 12px 18px 4px !important;
        font-weight: 700 !important;
    }

    /* Nav links */
    .main-sidebar .nav-link {
        color: rgba(255,255,255,.72) !important;
        border-radius: 10px !important;
        margin: 2px 10px !important;
        padding: 9px 14px !important;
        font-size: .83rem !important;
        font-weight: 500 !important;
        display: flex !important;
        align-items: center !important;
        gap: 0 !important;
        transition: background .15s, color .15s !important;
    }
    .main-sidebar .nav-link i {
        width: 24px !important;
        font-size: .85rem !important;
        color: rgba(255,255,255,.5) !important;
        flex-shrink: 0 !important;
        transition: color .15s !important;
    }
    .main-sidebar .nav-link p {
        color: inherit !important;
        font-size: inherit !important;
        margin-left: 6px !important;
    }
    .main-sidebar .nav-link:hover,
    .main-sidebar .nav-item.menu-open > .nav-link {
        background: rgba(255,255,255,.1) !important;
        color: #ffffff !important;
    }
    .main-sidebar .nav-link:hover i {
        color: #60a5fa !important;
    }
    .main-sidebar .nav-link.active {
        background: linear-gradient(90deg, rgba(59,130,246,.35), rgba(59,130,246,.15)) !important;
        color: #ffffff !important;
        border-left: 3px solid #3b82f6 !important;
        padding-left: 11px !important;
    }
    .main-sidebar .nav-link.active i { color: #60a5fa !important; }

    /* Treeview sub-menu */
    .main-sidebar .nav-treeview {
        padding: 2px 0 !important;
    }
    .main-sidebar .nav-treeview .nav-link {
        margin-left: 16px !important;
        font-size: .8rem !important;
        padding: 7px 14px !important;
        color: rgba(255,255,255,.55) !important;
    }
    .main-sidebar .nav-treeview .nav-link:hover {
        background: rgba(255,255,255,.08) !important;
        color: #fff !important;
    }
    .main-sidebar .nav-treeview .nav-link.active {
        border-left: 2px solid #3b82f6 !important;
        background: rgba(59,130,246,.2) !important;
        color: #93c5fd !important;
    }

    /* Logout nav item */
    .sidebar-logout-item .nav-link {
        color: rgba(248,113,113,.8) !important;
        margin-top: 8px !important;
    }
    .sidebar-logout-item .nav-link:hover {
        background: rgba(248,113,113,.15) !important;
        color: #fca5a5 !important;
    }
    .sidebar-logout-item .nav-link i { color: rgba(248,113,113,.7) !important; }

    /* Sidebar scrollbar */
    .sidebar { overflow-y: auto; overflow-x: hidden; }
    .sidebar::-webkit-scrollbar { width: 4px; }
    .sidebar::-webkit-scrollbar-track { background: transparent; }
    .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }

    /* ── Content wrapper ────────────────────────────────────────── */
    .content-wrapper { background: #f0f2f8 !important; }

    /* ── Cards (global override) ────────────────────────────────── */
    .card {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 10px rgba(0,0,0,.06) !important;
    }
    .card-header {
        background: #fff !important;
        border-bottom: 1px solid #f1f5f9 !important;
        border-radius: 12px 12px 0 0 !important;
        padding: 14px 18px !important;
        font-weight: 700 !important;
        font-size: .88rem !important;
        color: #1e293b !important;
    }
    .card-body { padding: 18px !important; }
    .card-footer {
        background: #f8fafc !important;
        border-top: 1px solid #f1f5f9 !important;
        border-radius: 0 0 12px 12px !important;
    }

    /* ── Buttons (global softer) ────────────────────────────────── */
    .btn {
        border-radius: 8px !important;
        font-size: .82rem !important;
        font-weight: 600 !important;
        padding: 6px 14px !important;
    }
    .btn-primary {
        background: #2563eb !important;
        border-color: #2563eb !important;
    }
    .btn-primary:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }
    .btn-success {
        background: #16a34a !important;
        border-color: #16a34a !important;
    }

    /* ── Tables (global softer) ─────────────────────────────────── */
    .table thead th {
        background: #f8fafc !important;
        font-size: .73rem !important;
        text-transform: uppercase !important;
        letter-spacing: .8px !important;
        color: #64748b !important;
        border-top: none !important;
        border-bottom: 1px solid #e8ecf4 !important;
        padding: 10px 14px !important;
        font-weight: 600 !important;
    }
    .table td {
        border-color: #f1f5f9 !important;
        vertical-align: middle !important;
        font-size: .82rem !important;
        color: #374151 !important;
        padding: 10px 14px !important;
    }

    /* ── Breadcrumb ─────────────────────────────────────────────── */
    .content-header .breadcrumb { background: transparent !important; }
    .content-header h1 { font-size: 1.15rem !important; font-weight: 700 !important; color: #1e293b !important; }
    .breadcrumb-item { font-size: .78rem !important; }

    /* ── Footer ─────────────────────────────────────────────────── */
    .main-footer {
        background: #fff !important;
        border-top: 1px solid #e8ecf4 !important;
        font-size: .78rem !important;
        color: #94a3b8 !important;
        padding: 10px 20px !important;
    }

    /* ── Badge overrides ────────────────────────────────────────── */
    .badge {
        border-radius: 6px !important;
        font-size: .7rem !important;
        padding: 3px 8px !important;
        font-weight: 600 !important;
    }

    /* ── Responsive ─────────────────────────────────────────────── */
    @media (max-width: 576px) {
        .nav-link-email { max-width: 100%; }
        .content-wrapper { padding: 10px !important; }
    }
    </style>
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">
    
        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>


@stop

@section('adminlte_js')
    {{-- Include Firebase Initialization --}}
    @livewireScripts
    @stack('js')
    @yield('js')
@stop
