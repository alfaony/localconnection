@extends('adminlte::page')

@section('title', 'Pass Checkings')

@section('content_header')
    <h1>Pass Checkings</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Form for Create and Edit -->
         @canAccess('store','pass_checkings')
         @canAccess('update','pass_checkings')
        <form 
            action="{{ @$editing ? route('pass-checking.update', @$editing->id) : route('pass-checking.store') }}" 
            method="POST" 
            enctype="multipart/form-data">
            @csrf
            @if(@$editing)
                @method('PUT')
            @endif

            <h5>{{ @$editing ? 'Edit Pass Checking' : 'Create Pass Checking' }}</h5>
            
            <div class="mb-3">
                <label for="name" class="form-label">Agenda</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    class="form-control" 
                    value="{{ old('name', @$editing->name ?? '') }}" 
                    required
                >
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Tanggal</label>
                <input 
                    type="date" 
                    name="date" 
                    id="date" 
                    class="form-control" 
                    value="{{ old('date', @$editing ? Carbon\Carbon::parse(@$editing->date)->format('Y-m-d') : date('Y-m-d') ) }}" 
                    min="{{ @$editing ? Carbon\Carbon::parse(@$editing->date)->format('Y-m-d') : date('Y-m-d') }}" 
                    @if(@$editing) {{ !@$editing->isDeleted() ? 'readonly' : 'required' }} @else {{'required'}}  @endif
                >
            </div>

            <div class="mb-3">
                <label for="start_time" class="form-label">Waktu Mulai</label>
                <input 
                    type="time" 
                    name="start_time" 
                    id="start_time" 
                    class="form-control" 
                    value="{{ old('start_time', Carbon\Carbon::parse(@$editing->start_time)->format('H:i') ??  '') }}" 
                    min="{{ @$editing ? Carbon\Carbon::parse(@$editing->start_time)->format('H:i') : date('H:i') }}"
                    @if(@$editing) {{ !@$editing->isDeleted() ? 'readonly' : 'required' }} @else {{'required'}}  @endif
                >
            </div>

            <div class="mb-3">
                <label for="end_time" class="form-label">Waktu Selesai</label>
                <input 
                    type="time" 
                    name="end_time" 
                    id="end_time" 
                    class="form-control" 
                    value="{{ old('end_time', Carbon\Carbon::parse(@$editing->end_time)->format('H:i') ??  '') }}" 
                    min="{{ @$editing ? Carbon\Carbon::parse(@$editing->end_time)->format('H:i') : date('H:i') }}"
                    @if(@$editing) {{ !@$editing->isDeleted() ? 'readonly' : 'required' }} @else {{'required'}}  @endif
                >
            </div>

            @if(@$editing && !empty(@$editing->pictures))
    <div class="mb-3">
        <label class="form-label">Existing Pictures</label>
        <div class="d-flex flex-wrap gap-2" id="pictures-container">
            @foreach(@$editing->pictures as $key => $picture)
                <div class="position-relative" id="picture-{{ $key }}">
                    <img src="{{ $picture }}" alt="Picture" class="img-thumbnail" width="100px">
                    <button 
                        type="button" 
                        class="btn btn-danger-custom btn-sm position-absolute top-0 end-0" 
                        onclick="deletePicture('{{ $key }}', '{{ $picture }}')"
                        style="border-radius: 50%;"
                    >
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Input untuk menyimpan daftar gambar yang dihapus -->
<input type="hidden" name="delete_pictures" id="delete_pictures">
            <div class="mb-3">
                <label for="pictures" class="form-label">Upload Pictures</label>
                <input 
                    type="file" 
                    name="pictures[]" 
                    id="pictures" 
                    class="form-control" 
                    multiple 
                    accept="image/*"
                >
                <small class="form-text text-muted">You can upload multiple pictures.</small>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ @$editing ? 'Update' : 'Save' }}
            </button>
            <a href="{{ route('pass-checking.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
        @endcanAccess
        @endcanAccess

        <hr>

        <!-- Pass Checking List -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Agenda</th>
                    <th>Tanggal</th>
                    <th>Foto</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passCheckings as $passChecking)
                    <tr>
                        <td>{{ $loop->iteration + $passCheckings->firstItem() - 1 }}</td>
                        <td>{{ $passChecking->name ?? 'N/A' }}</td>
                        <td>
                            {{ $passChecking->date ? \Carbon\Carbon::parse($passChecking->date)->format('d-m-Y') : '-' }}
                            <br>
                            {{ $passChecking->start_time ? \Carbon\Carbon::parse($passChecking->start_time)->format('H:i') : '-' }} -
                            {{ $passChecking->end_time ? \Carbon\Carbon::parse($passChecking->end_time)->format('H:i') : '-' }}
                        </td>
                        <td width="40%">
                            @if($passChecking->pictures)
                                @foreach($passChecking->pictures as $picture)
                                    <img src="{{ $picture }}" alt="Picture" class="img-thumbnail" width="200">
                                @endforeach
                            @else
                                No pictures
                            @endif
                        </td>
                        <td>
                            @canAccess('show','pass_checkings')
                            <a href="{{ route('pass-checking.show', $passChecking->id) }}" class="btn btn-info btn-sm">
                                <i class="fa fa-eye"></i>
                            </a>
                            @endcanAccess

                            @canAccess('edit','pass_checkings')
                            <a href="{{ route('pass-checking.edit', $passChecking->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-edit"></i>
                            </a>
                            @endcanAccess

                            @canAccess('destroy','pass_checkings')
                            @if($passChecking->isDeleted())
                            <form action="{{ route('pass-checking.destroy', $passChecking->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            @endif
                            @endcanAccess
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No Pass Checkings Found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $passCheckings->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
    let deletedPictures = []; // Array untuk menyimpan gambar yang dihapus
    
    function deletePicture(key, picture) {
        // Tambahkan key gambar ke array deletedPictures
        deletedPictures.push(key);
        
        // Update nilai input hidden dengan JSON dari array deletedPictures
        document.getElementById('delete_pictures').value = JSON.stringify(deletedPictures);
    
        // Hapus elemen gambar dari tampilan
        const pictureElement = document.getElementById(`picture-${key}`);
        if (pictureElement) {
            pictureElement.remove();
        }
    }
</script>
@endsection
@section('css')
<style>
    .btn-danger-custom 
    {
        background-color: #dc3545;
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
        cursor: pointer;
    }

    .btn-danger i {
        font-size: 12px;
    }
</style>
@endsection