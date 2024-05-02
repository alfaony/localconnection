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
        <div class="card-header">{{ isset($check) ? 'Kontrol Keamanan' : 'Kontrol Keamanan' }}</div>
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

            <form action="{{ isset($check) ? route('security-check.update', $check->slug) : route('security-check.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($check))
                @method('PUT')
                @endif


                <label for="photos" class="form-label">{{ isset($check) ? 'Foto Sore' : 'Foto Pagi' }}</label>
                <div id="photo-inputs" class="mb-3">
                    <div class="input-group mb-3">
                        <input type="file" class="form-control" id="photo" name="photos[]" accept="image/*" capture="environment" onchange="compressImage(event)">
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
                    <input type="file" class="form-control" name="photos[]" accept="image/*" capture="environment" required>
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
function compressImage(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    reader.onload = function (event) {
        const img = new Image();
        img.src = event.target.result;
        img.onload = function () {
            const elem = document.createElement('canvas');
            const scaleFactor = 0.5; // Adjust scaleFactor to get smaller images
            elem.width = img.width * scaleFactor;
            elem.height = img.height * scaleFactor;
            const ctx = elem.getContext('2d');
            ctx.drawImage(img, 0, 0, elem.width, elem.height);
            ctx.canvas.toBlob((blob) => {
                const newFile = new File([blob], file.name, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
                // Replace the input file with new compressed file
                event.target.files[0] = newFile;
            }, 'image/jpeg', 0.75); // Adjust quality from 0 to 1
        };
    };
    reader.readAsDataURL(file);
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
