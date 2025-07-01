@extends('adminlte::page')

@section('title', 'Daftar Rapat')

@section('content')
    @include('components.alert')
    @livewire('mom-table')
@stop

@section('js')
    @livewireScripts
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>
    <script>
        // Confirmation for delete action
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin menghapus MoM ini?')) {
                    this.submit();
                }
            });
        });
    </script>
    <script>
        // Handle success messages
        @if (session('success'))
            Swal.fire({
                title: 'Success!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit the form if confirmed
                    }
                });
            });
        });
    </script>
@stop

@section('css')
    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css">
     <style>
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header-section {
            background: linear-gradient(120deg, #4361ee, #3a0ca3);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-mom {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            background-color: white;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
        }
        
        .card-mom:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }
        
        .mom-header {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .mom-title {
            font-weight: 700;
            color: #212529;
            margin-bottom: 0.25rem;
            font-size: 1.25rem;
        }
        
        .mom-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .mom-detail {
            padding: 1.5rem;
        }
        
        .detail-item {
            display: flex;
            margin-bottom: 0.8rem;
            align-items: flex-start;
        }
        
        .detail-icon {
            color: var(--primary-color);
            min-width: 24px;
            margin-right: 12px;
            margin-top: 3px;
        }
        
        .detail-content {
            flex: 1;
        }
        
        .mom-notes {
            background-color: #f8f9fa;
            border-left: 3px solid var(--accent-color);
            padding: 1rem;
            border-radius: 0 4px 4px 0;
            margin-top: 1rem;
            font-size: 0.95rem;
        }
        
        .btn-action {
            min-width: 90px;
            margin: 0 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state {
            background-color: white;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: var(--card-shadow);
        }
        
        .search-box {
            position: relative;
            max-width: 500px;
            margin: 0 auto 1.5rem;
        }
        
        .search-box .form-control {
            border-radius: 50px;
            padding-left: 45px;
            height: 48px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
        }
        
        .search-box i {
            position: absolute;
            left: 20px;
            top: 14px;
            color: #adb5bd;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .badge-project {
            background-color: #e0f4ff;
            color: #0369a1;
            font-weight: 500;
            padding: 0.35rem 0.8rem;
            border-radius: 50px;
        }
        
        .badge-meeting {
            background-color: #f0f9ff;
            color: #0c4a6e;
            font-weight: 500;
            padding: 0.35rem 0.8rem;
            border-radius: 50px;
        }
        
        .action-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        
        @media (max-width: 768px) {
            .action-container {
                justify-content: flex-start;
                margin-top: 1rem;
            }
            
            .btn-action {
                flex: 1;
                max-width: 100px;
            }
            
            .mom-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@stop