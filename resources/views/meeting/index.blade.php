@extends('adminlte::page')

@section('title', 'Daftar Rapat')

@section('content_header')
    <h1>Daftar Rapat</h1>
@stop

@section('content')
    @livewire('meeting-table')
@stop

@section('css')
    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css">
    {{-- <style>
        /* CSS untuk menyesuaikan lebar kolom */
        table th,
        table td {
            word-wrap: break-word;
            /* Memungkinkan teks panjang terpotong dan melanjutkan ke baris berikutnya */
            white-space: nowrap;
            /* Mencegah teks dari melipat pada satu baris */
            padding: 8px;
            /* Padding untuk memastikan jarak antara teks dan border */
        }

        table td {
            max-width: 200px;
            /* Tentukan lebar maksimal untuk setiap kolom */
            overflow: hidden;
            /* Sembunyikan teks yang lebih panjang dari lebar kolom */
            text-overflow: ellipsis;
            /* Menampilkan elipsis jika teks lebih panjang dari kolom */
        }

        /* Menetapkan lebar kolom tertentu */
        .column-no {
            width: 50px;
        }

        .column-nama-rapat {
            width: 200px;
        }

        .column-agenda-rapat {
            width: 250px;
        }

        .column-status {
            width: 100px;
        }

        .column-tanggal {
            width: 120px;
        }

        .column-waktu {
            width: 150px;
        }

        .column-jenis-rapat {
            width: 150px;
        }

        .column-aksi {
            width: 120px;
        }
    </style> --}}
    
@stop

@section('js')
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>
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