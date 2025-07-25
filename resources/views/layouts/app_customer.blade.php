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
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .main-content {
            min-height: calc(100vh - 120px);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: 40px;
        }
    </style>
    
    @stack('styles')
    @livewireStyles
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-wifi me-2"></i>
                @yield('title')
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            {{-- 
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/login"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                    </li>
                </ul>
            </div>
            --}}
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <small class="text-muted">Fast and reliable internet connection</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
     @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    
    @stack('scripts')
</body>
</html>