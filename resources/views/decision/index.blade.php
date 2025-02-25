
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
                        <th>Aksi</th>
                    </tr>
                </thead>

                @forelse($decisions as $a)
                <tr>
                    <td>{{ $a->user->name ?? "" }}</td>
                    <td>{{ $a->question }}</td>
                    <td style="white-space: nowrap;">
                        @canAccess('update','decisions')
                        <button class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#shareModal-{{$a->id}}" data-url="{{ route('decision.show', $a) }}">
                            <i class="fa fa-share-alt"></i>
                        </button>
                        <!-- Modal -->
                        <!-- Modal Share -->
                        <div class="modal fade" id="shareModal-{{$a->id}}" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="shareModalLabel">Bagikan Keputusan</h5>
                                        <button type="button" class="close btnCloseModal" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Input URL -->
                                        <div class="form-group">
                                            <label for="decisionLink">Link:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="decisionLink" readonly value="{{ route('decision.show', $a) }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary copyLinkBtn" id="copyLinkBtn">Copy Link</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Multi-Select User (Select2) -->
                                            <form action="{{ route('decision.update',$a->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group">
                                                <label for="users">Bagikan ke User:</label>
                                                <div class="input-group">
                                                <select class="form-control select2" id="users" name="users[]" multiple="multiple" style="width: 100%;">
                                                    @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ in_array($user->id, json_decode($a->user_sharing ?? '[]')) ? 'selected' : '' }}>{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                                </div>
                                            </div>

                                            <button class="btn-model-click btn btn-primary" >Perbarui Sharing</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
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

@stop
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2').select2({
            dropdownParent: '.modal',
            placeholder: "Pilih user",
            allowClear: true
        });

        // Set URL ketika modal dibuka
        $('#shareModal').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let url = button.data('url');

            $('#decisionLink').val(url);
        });

        // Copy URL ke clipboard
        $('.copyLinkBtn').on('click', function() {
            let copyText = document.getElementById("decisionLink");
            copyText.select();
            document.execCommand("copy");
            
            $(".btnCloseModal").click();
        });

    });
</script>
<script>
    $(document).ready(function () 
    {
        $('.select2').select2({
            width: '100%',
        });

        let price_buy = document.getElementById("price_buy").value;
        if (price_buy) 
        {
            document.getElementById("price_buy_show").value = price_buy;
            formatRupiahFormat(document.getElementById("price_buy_show"),"price_buy"); // Format default value
        }

        let price_sell = document.getElementById("price_sell").value;
        if (price_sell) 
        {
            document.getElementById("price_sell_show").value = price_sell;
            formatRupiahFormat(document.getElementById("price_sell_show"),"price_sell"); // Format default value
        }
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

