@extends('adminlte::page')

@section('content')
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
<div class="container mt-4">
    <div class="card">
        <div class="card-header">{{ isset($check) ? 'Kontrol Cctv' : 'Kontrol Cctv' }}</div>
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ isset($check) ? route('cctv-check.update', $check->slug) : route('cctv-check.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($check))
                @method('PUT')
                @endif

                <div id="photo-inputs" class="mb-3">
                    <label for="photos" class="form-label">Foto</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="descriptions[]" placeholder="Deskripsi Foto" required>
                        <input type="file" class="form-control" id="photo" name="photos[]" accept="image/*"  onchange="compressAndAddImage(event)" required>
                        <button class="btn btn-danger remove-photo" type="button"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <button type="button" class="btn btn-success add-photo"><i class="fa fa-plus"></i> Foto</button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">{{ isset($check) ? 'Ubah' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function() {
        let photoIndex = 1; // Start with one already present

        $('.add-photo').click(function() {
            if (photoIndex < 10) {
                $('#photo-inputs').append(`
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="descriptions[]" placeholder="Deskripsi Foto" required>
                        <input type="file" class="form-control" name="photos[]" accept="image/*"  onchange="compressAndAddImage(event)" required>
                        <button class="btn btn-danger remove-photo" type="button"><i class="fa fa-minus"></i></button>
                    </div>
                `);
                photoIndex++;
            } else {
                alert('Maksimal 10 Foto');
            }
        });

        $(document).on('click', '.remove-photo', function() {
            $(this).closest('.input-group').remove();
            photoIndex--;
        });
    });
</script>
<script>
function compressAndAddImage() {
    const fileInput = event.target;

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
                const file = new File([blob], "compressed_"+fileInput.files[0].name, {
                    type: 'image/jpeg',
                    quality: 0.8 // Lowering the quality to reduce file size
                });

                // Update the file input with the compressed image file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                // Update the preview image
            }, 'image/jpeg', 0.5); // Lowering quality setting here
        }
    }
}
</script>

@endsection

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
    </style>
@stop
