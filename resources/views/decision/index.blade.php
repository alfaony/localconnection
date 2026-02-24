
@extends('adminlte::page')

@section('content_header')
    <h1>Hasil Keputusan</h1>
@stop

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Keputusan Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Keputusan Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Keputusan Berhasil Terhapus</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            <ul>
                <li>{{ session('error') }}</li>
            </ul>
        </div>
    @endif
</div>


<div class="card mt-4">
    <div class="card-body">
        <form action="{{ route('decision.index') }}" method="get">
            <div class="d-flex flex-row-reverse">
                <div class="p-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
                <div class="p-2">
                    <input type="text" name="search" class="form-control" placeholder="Search">
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Pertanyaan</th>
                        <th>Nominal</th>
                        <th>Vendor Eksternal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                @forelse($decisions as $a)
                <tr>
                    <td>{{ $a->user->name ?? "" }}</td>
                    <td>{{ Str::limit($a->question, 60) }}</td>
                    <td style="white-space:nowrap;">
                        @if($a->nominal)
                            Rp {{ number_format($a->nominal, 0, ',', '.') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($a->consult_vendor)
                            <span class="badge badge-warning">{{ $a->consult_vendor }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td style="white-space: nowrap;">
                        @canAccess('update','decisions')
                        <button class="btn btn-secondary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#shareModal" 
                                data-id="{{ $a->id }}"
                                data-url="{{ route('decision.show', $a) }}"
                                data-urlupdate="{{ route('decision.update', $a) }}"
                                data-users="{{ json_encode(json_decode($a->user_sharing ?? '[]')) }}">
                            <i class="fa fa-share-alt"></i>
                        </button>
                        @endcanAccess
                        <!-- end Model -->
                        @canAccess('show','decisions')
                        <a href="{{ route('decision.show',$a) }}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                        @endcanAccess

                        @canAccess('destroy','decisions')
                        @if(!$a->is_approve)
                        <form method="post" action="{{ route('decision.destroy',$a) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            @canAccess('destroy','products')
                            <button onclick="return window.confirm('{{ __('Apakah Anda Yakin Hapus Data ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                            @endcanAccess
                        </form>
                        @endif
                        @endcanAccess
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">
                        <center>Data Kosong</center>
                    </td>
                </tr>
                @endforelse
            </table>
            {{ $decisions->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
<!-- Modal Share (Hanya satu modal yang digunakan) -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Bagikan Keputusan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Input URL -->
                <div class="form-group">
                    <label for="decisionLink">Link:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="decisionLink" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="copyLinkBtn">Copy Link</button>
                        </div>
                    </div>
                </div>

                <!-- Multi-Select User (Select2) -->
                <form action="" method="POST" id="shareForm">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="users">Bagikan ke User:</label>
                        <div class="input-group">
                            <select class="form-control select2" id="users" name="users[]" multiple="multiple" style="width: 100%;">
                                <!-- Opsi ini akan diisi melalui JavaScript -->
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Perbarui Sharing</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2({
        placeholder: "Pilih pengguna untuk dibagikan",
        allowClear: true
    });

    // Menangani klik tombol untuk mengisi modal dengan data dinamis
    $('#shareModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Tombol yang diklik
        var url = button.data('url'); // Mendapatkan URL
        var urlupdate = button.data('urlupdate'); // Mendapatkan URL untuk update
        var id = button.data('id'); // Mendapatkan ID keputusan
        var selectedUserIds = button.data('users'); // Mendapatkan ID pengguna yang dipilih (array JSON)

        // Mengisi link di modal
        $('#decisionLink').val(url); // Menampilkan link dalam input readonly

        // Mengatur form action untuk memperbarui keputusan
        $('#shareForm').attr('action', urlupdate); // Atur action form sesuai ID

        var users = @json($users); // Mengambil data pengguna dari PHP dalam format JSON

        // Menyiapkan elemen select2
        var select2Element = $('#users');

        // Mengosongkan opsi yang ada sebelumnya dan menambahkan opsi dinamis
        select2Element.empty();

        // Menggunakan data users untuk membuat opsi dalam select2
        users.forEach(function(user) {
            var isSelected = selectedUserIds.includes(user.id); // Memeriksa apakah user sudah dipilih
            var option = new Option(user.name, user.id, isSelected, isSelected);
            select2Element.append(option); // Menambahkan opsi ke select2
        });

        // Menandai nilai yang sudah dipilih di select2
        select2Element.val(selectedUserIds).trigger('change');
        
        // Menginisialisasi select2
        select2Element.select2({
            placeholder: "Pilih pengguna untuk dibagikan",
            allowClear: true
        });
    });

    // Tombol untuk menyalin link
    $('#copyLinkBtn').on('click', function() {
        var decisionLink = document.getElementById('decisionLink');
        decisionLink.select();
        decisionLink.setSelectionRange(0, 99999); // Untuk perangkat mobile
        document.execCommand('copy');
    });
});
</script>
@stop
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 10px;
            border-radius: 5px;
        }

        .btn-custom {
            background-color: #007bff;
            color: #ffffff;
            border-radius: 4px;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .pagination > li > a {
            color: #007bff;
            background-color: transparent;
            border: none;
        }

        .pagination > .active > a {
            background-color: #007bff;
            color: #ffffff;
        }
        .select2-selection__rendered
        {
            line-height: 31px !important;
        }
        .select2-container .select2-selection--single
        {
            height: 35px !important;
        }
        .select2-selection__arrow {
            height: 34px !important;
        }

        .select2-selection__choice
        {
            background-color: #007bff !important;
            border: 1px solid #007bff !important;
        }

        .select2-selection__choice__remove
        {
            color: #fe0700 !important;
            border: 1px solid #007bff !important;
        }
    </style>
@stop

