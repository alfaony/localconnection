@extends('adminlte::page')

@section('content_header')
    <h2>Absen Masuk</h2>
@stop

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('attendance.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="clock_in">Jam Masuk</label>
                            <p id="clock_in" class="form-control-plaintext"></p>
                            <input type="hidden" name="clock_in" id="hidden_clock_in">
                        </div>
                        <div class="form-group">
                            <label for="image">Foto</label>
                            <input type="file" class="form-control-file" id="photo" name="pic_in" accept="image/*" capture="user" onchange="compressAndPreviewImage();" required>
                            <img id="photo-preview" src="#" alt="Photo Preview" style="display:none;" class="img-fluid mt-3"/>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let now = new Date();
        let hours = String(now.getHours()).padStart(2, '0');
        let minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock_in').innerText = hours + ':' + minutes;
        document.getElementById('hidden_clock_in').value = hours + ':' + minutes;
    });
</script>
@stop
