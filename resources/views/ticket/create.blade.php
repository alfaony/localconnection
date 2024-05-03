<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membuat Tiket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .container { max-width: 600px; }
    </style>
</head>
<body>
    <div class="container">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Tiket Berhasil Dibuat</div>
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
    </div>
        <div class="card">
            <div class="card-header">Membuat Tiket</div>
            <div class="card-body">
                <form action="{{ route('bos-ticket.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- 
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="contact" name="contact" required>
                    </div>
                    --}}
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subjek</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Konten</label>
                        <input type="text" class="thriveEditor form-control" id="description_content" data-ids="content"  name="content">
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Ambil Foto</label>
                        <input type="file" class="form-control" id="photo" name="path" accept="image/*" capture="environment" onchange="compressAndPreviewImage();" required>
                        <small class="text-muted">Klik untuk mengambil foto menggunakan kamera.</small>
                        <img id="photo-preview" src="#" alt="Photo Preview" style="display:none;" class="img-fluid mt-3"/>
                        @error('photo')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        {!! NoCaptcha::renderJs() !!}
                        {!! NoCaptcha::display() !!}
                    </div>
                    <button type="submit" class="btn btn-primary">Buat Tiket</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="{{ asset('js/thriveEditor.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
</body>
</html>
