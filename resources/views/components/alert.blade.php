
@if(Session::has('store'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Data berhasil ditambahkan.

</div>
@endif

@if(Session::has('update'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Data berhasil diperbarui.

</div>
@endif

@if(Session::has('delete'))
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Data berhasil dihapus.

</div>
@endif

@if(Session::has('submit'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Berhasil di Submit.
</div>
@endif

@if(Session::has('sign'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Berhasil di Sign.
</div>
@endif

@if(Session::has('approved'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Data berhasil di Setujui.
</div>
@endif

@if(Session::has('rejected'))
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Data berhasil di Tolak.
</div>
@endif

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> {{ Session::get('success') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <strong>Kesalahan!</strong> Periksa kembali input Anda.
    <ul class="mt-2">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

