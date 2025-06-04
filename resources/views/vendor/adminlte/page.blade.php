@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
    <style>
    .nav-link-email {
        white-space: normal; /* Izinkan teks untuk membungkus ke baris baru */
        display: block;      /* Membuat elemen span menjadi blok sehingga mudah membungkus */
        word-wrap: break-word; /* Membungkus kata panjang ke baris berikutnya */
    }

    /* Responsive adjustment for smaller screens */
    @media (max-width: 576px) {
        .nav-link-email {
            max-width: 100%; /* Mengisi lebar penuh pada layar kecil */
        }
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
    @include('partials.permission-fcm')
    @include('inbox.inboxscript')
    @stack('js')
    @yield('js')
@stop
