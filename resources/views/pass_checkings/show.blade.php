@extends('adminlte::page')

@section('title', 'Show Pass Checkings')

@section('content_header')
    <h1>Pass Checkings {{ $passChecking->name }}</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Pass Checking Details</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>Agenda:</strong>
            <p>{{ $passChecking->name ?? 'N/A' }}</p>
        </div>

        <div class="mb-3">
            <strong>Date:</strong>
            <p>{{ $passChecking->date ? \Carbon\Carbon::parse($passChecking->date)->format('d-m-Y') : '-' }}</p>
        </div>

        <div class="mb-3">
            <strong>Time:</strong>
            <p>
                Start: {{ $passChecking->start_time ? \Carbon\Carbon::parse($passChecking->start_time)->format('H:i') : '-' }} <br>
                End: {{ $passChecking->end_time ? \Carbon\Carbon::parse($passChecking->end_time)->format('H:i') : '-' }}
            </p>
        </div>

        <div class="mb-3">
            <strong>Pictures:</strong>
            <div class="d-flex flex-wrap gap-2">
                @if($passChecking->pictures && count($passChecking->pictures) > 0)
                    @foreach($passChecking->pictures as $key => $picture)
                        <div class="position-relative mr-2 mb-3" id="picture-{{ $key }}">
                            <img src="{{ $picture }}" alt="Picture" class="img-thumbnail" width="200">
                            <div class="d-flex justify-content-center mt-2">
                                <!-- Button to update image -->
                                <button 
                                    class="btn btn-primary btn-sm mr-1"
                                    data-toggle="modal" 
                                    data-target="#updateImageModal" 
                                    data-key="{{ $key }}" 
                                    data-url="{{ $picture }}"
                                    onclick="console.log('Modal triggered');"
                                >
                                    <i class="fa fa-edit"></i> Update
                                </button>

                                <!-- Button to delete image -->
                                <button 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this picture?') && deletePicture('{{ $key }}')"
                                >
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p>No pictures available.</p>
                @endif
            </div>
        </div>
        
        <div class="d-flex justify-content-start">
            <a href="{{ route('pass-checking.index') }}" class="btn btn-secondary">Back</a>
                <button 
                    class="btn btn-success btn-sm ml-2"
                    data-toggle="modal"
                    data-target="#addImageModal"
                >
                    <i class="fa fa-plus"></i> Add Image
                </button>
        </div>
    </div>
</div>

<!-- Add Image Modal -->
<div class="modal fade" id="addImageModal" tabindex="-1" role="dialog" aria-labelledby="addImageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addImageForm" method="POST" action="{{ route('pass-checking.update', $passChecking->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title" id="addImageModalLabel">Add Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newImage">Upload New Image</label>
                        <input type="file" class="form-control" id="newImage" name="pictures[]" accept="image/*" required multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Image Modal -->
<div class="modal fade" id="updateImageModal" tabindex="-1" role="dialog" aria-labelledby="updateImageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="updateImageForm" method="POST" action="{{ route('pass-checking.update', $passChecking->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateImageModalLabel">Update Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="image">Upload New Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <input type="hidden" id="updateKey" name="key">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Input for Delete -->
<form id="deletePictureForm" method="POST" action="{{ route('pass-checking.update', $passChecking->id) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="delete_pictures" id="delete_pictures" value="">
</form>
@endsection
@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const updateModal = document.getElementById('updateImageModal');
    const updateForm = document.getElementById('updateImageForm');
    const deletePicturesInput = document.getElementById('delete_pictures');
    const deletePictureForm = document.getElementById('deletePictureForm');
    let deletedPictures = []; // Array untuk menyimpan gambar yang dihapus

    // Populate modal with the selected picture data
    $('#updateImageModal').on('show.bs.modal', function (event) 
    {
        console.log('Modal is being shown'); // Debug
        const button = $(event.relatedTarget); // Tombol yang memicu modal
        const key = button.data('key'); // Ambil data-key
        const url = button.data('url'); // Ambil URL gambar (opsional)
        
        // Debug Key dan URL
        console.log('Key:', key, 'URL:', url);

        // Isi input hidden di form modal
        $(this).find('#updateKey').val(key);
    });

    // Function to delete picture
    window.deletePicture = function (key) {
        // Add the key to the deleted pictures array
        deletedPictures.push(key);
        deletePicturesInput.value = JSON.stringify(deletedPictures);

        // Submit the form to update
        deletePictureForm.submit();
    };
});

</script>
@endsection
@section('css')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
@endsection