@extends('adminlte::page')

@section('title', 'Manajemen Laptop Bekas')

@section('content_header')
    <h1>Manajemen Laptop Bekas</h1>
@stop

@section('content')
@canAccess('index','used_items')
    <div class="row">
        <div class="col-md-12">
            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            
            @livewire('used-laptop-table')
        </div>
    </div>
@endcanAccess
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="{{ asset('js/thriveEditor.js') }}"></script>
    @livewireScripts
    @stack('scripts')

    <script>
        $(document).ready(function() {
            $('.alert').delay(3000).fadeOut();
        });


        function formatRupiahFormat(input, inputNonFormat) 
        {
            let numStr = input.value.toString().replace(/[^,\d]/g, '');
            let split = numStr.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

            if (numStr === "" || parseInt(numStr) === 0) {
                input.value = '';
                numStr = 0;
            } else {
                // Menghapus angka 0 di depan jika input diawali dengan 0
                rupiah = rupiah.replace(/^0+/, '');
                input.value = 'Rp '+rupiah;
            }

            // Update 'salary' input with non-formatted number
            document.getElementById(inputNonFormat).value = parseInt(numStr);
        }
    </script>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .spec-item {
            padding: 8px 12px;
            border-radius: 5px;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
        }
        .history-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 3px solid #6c757d;
        }
        .sale-info {
            background: #e8f7f0;
            padding: 15px;
            border-radius: 5px;
            border-left: 3px solid #28a745;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        .card-header.bg-primary {
            background: linear-gradient(to right, #0062cc, #007bff);
        }
    </style>
@stop