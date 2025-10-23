@extends('adminlte::page')

@section('content')
<div class="container py-3">
    <div class="card">
        <div class="card-header">Tiket Detail</div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    {{-- 
                    <div class="mb-3">
                        <label><strong>Contact:</strong></label>
                        <p>{{ $ticket->contact }}</p>
                    </div>
                    --}}
                    <div class="mb-3">
                        <label><strong>Subjek:</strong></label>
                        <p>{{ $ticket->subject }}</p>
                    </div>
                    <div class="mb-3">
                        <label><strong>Status:</strong></label>
                        <p>{{ ucfirst($ticket->status) }}</p>
                    </div>
                    <div class="mb-3">
                        <label><strong>Konten:</strong></label>
                        <p>{!! $ticket->content !!}</p>
                    </div>
                    <div class="mb-3">
                        <label><strong>Gambar Laporan:</strong></label>
                        <img src="{{ s3_asset(true,10,'ticket/' .$ticket->path) }}" class="img-fluid mb-2" alt="Foto Laporan">
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Laporan Tiket</h5>
                    @if($ticket->note)
                        <!-- Menampilkan laporan yang sudah ada -->
                        <div>
                            <p><strong>Catatan Hasil:</strong> {!! $ticket->note !!}</p>
                        </div>
                    @else
                    @canAccess('update','tickets')
                    <form  action="{{ route('ticket.update',$ticket->slug) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        <div class="mb-3">
                            <label for="note" class="form-label">Catatan Hasil Tiket</label>
                            <input type="text" class="thriveEditor form-control" id="description_note" data-ids="note"  name="note">
                            @error('note')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                    @endcanAccess
                    @endif
                </div>
            </div>
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('ticket.index') }}" class="btn btn-secondary">Kembali Tiket</a>
        </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
function compressAndPreviewImage() {
    const fileInput = document.getElementById('photo');
    const preview = document.getElementById('photo-preview');

    if (!fileInput.files[0]) {
        preview.src = "";
        return;
    }

    const reader = new FileReader();
    reader.readAsDataURL(fileInput.files[0]);
    reader.onload = function (event) {
        const imgElement = document.createElement("img");
        imgElement.src = event.target.result;
        imgElement.onload = function (e) {
            const canvas = document.createElement("canvas");
            const MAX_WIDTH = 800; // Define the maximum width of the image

            const scaleSize = MAX_WIDTH / e.target.width;
            canvas.width = MAX_WIDTH;
            canvas.height = e.target.height * scaleSize;

            const ctx = canvas.getContext("2d");
            ctx.drawImage(e.target, 0, 0, canvas.width, canvas.height);
            ctx.canvas.toBlob((blob) => {
                const file = new File([blob], "compressed_image.jpg", {
                    type: 'image/jpeg',
                    quality: 0.8 // Lowering the quality to reduce file size
                });

                // Update the file input with the compressed image file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                // Update the preview image
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function () {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                }
            }, 'image/jpeg', 0.6); // Lowering quality setting here
        }
    }
}
</script>

@endsection
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endsection
