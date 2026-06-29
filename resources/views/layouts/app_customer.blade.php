<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Header/Navbar dengan background merah */
        .navbar {
            background-color: #ffffff !important;
            padding: 1rem 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.3rem;
        }
        
        .navbar-brand:hover {
            color: #f0f0f0 !important;
        }
        
        .main-content {
            min-height: calc(100vh - 180px);
            padding: 20px 0;
        }
        
        /* Footer dengan background merah */
        .footer {
            background-color: #F8F9FA;
            border-top: 1px solid #ddd;
        }
        
        .footer p, .footer small {
            color: #212529;
            margin: 0;
        }
        
        .footer small {
            opacity: 0.9;
        }

    .header-logo {
        text-align: center;
    }

    .header-logo img {
        max-width:400px;
        height: auto;
        background: white;
        border-radius: 8px;
    }
    </style>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @stack('styles')
    @livewireStyles
</head>
<body>
    <!-- Navbar dengan background merah -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <div class="header-logo">
                    <img src="{{ asset('logo/logo hikarinet 2.svg') }}" alt="Company Logo">
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <!-- Main Content dengan background putih bersih -->
    <main class="main-content">
        <div id="main-container" class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer dengan background merah -->
    <footer class="footer">
        <div class="container text-center mt-3 pt-3">
            <p class="mb-1">&copy; {{ date('Y') }} @yield('title').</p>
            <small>Fast and reliable internet connection</small>
        </div>
    </footer>

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    @livewireScripts
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/location-map.js') }}"></script>
    @stack('scripts')
</body>
</html>