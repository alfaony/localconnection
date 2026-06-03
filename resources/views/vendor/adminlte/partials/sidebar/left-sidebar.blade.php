<aside class="main-sidebar sidebar-custom elevation-0">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    @php( $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout') )

    @if (config('adminlte.use_route_url', false))
        @php( $logout_url = $logout_url ? route($logout_url) : '' )
    @else
        @php( $logout_url = $logout_url ? url($logout_url) : '' )
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar sidebar-custom-inner">
        {{-- User info panel --}}
        @auth
        <a href="/">
        <div class="sidebar-user-panel">
            <div class="sidebar-user-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                <div class="sidebar-user-role">{{ Auth::user()->role->name ?? 'User' }}</div>
            </div>
        </div>
        </a>
        @endauth

        <nav class="pt-1">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>

                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')

                {{-- Logout --}}
                <li class="nav-item sidebar-logout-item">
                    <a class="nav-link" href="#" onclick="event.preventDefault(); logoutUser();">
                        <i class="fas fa-fw fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

</aside>
