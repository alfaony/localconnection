@extends('adminlte::page')

@section('title', 'Visi & Misi')

@section('content_header')
    <h1>Visi & Misi</h1>
@stop

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3>Visi</h3>
            @if($vision)
                @canAccess('update','visions')
                <button class="btn btn-primary" data-toggle="modal" data-target="#editVisionModal"><i class="fa fa-edit"></i> Visi</button>
                @endcanAccess
            @else
            @canAccess('store','visions')
                <button class="btn btn-primary" data-toggle="modal" data-target="#addVisionModal"><i class="fa fa-plus"></i> Visi</button>
            @endcanAccess
            @endif
        </div>
        <div class="card-body">
            @if($vision)
                <p>{{ @$vision->vision }}</p>
            @else
                <p>Belum ada visi.</p>
            @endif
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3>Misi</h3>
            @if(!$vision)
                <div class="alert alert-warning" role="alert">
                    Tambahkan visi terlebih dahulu sebelum menambahkan misi.
                </div>
            @else
            @canAccess('store','missions')
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addMissionModal"><i class="fa fa-plus"></i> Misi</button>
            @endcanAccess
            @endif
        </div>
        <div class="card-body">
            @if($vision && @$vision->missions->count())
                <ul class="list-group">
                    @foreach(@$vision->missions as $mission)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $mission->mission }}
                            <div>
                                @canAccess('update','missions')
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editMissionModal{{ $mission->id }}"><i class="fa fa-edit"></i></button>
                                @endcanAccess
                                @canAccess('destroy','missions')
                                <form action="{{ route('mission.destroy', $mission->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus misi ini?')"><i class="fa fa-trash"></i></button>
                                </form>
                                @endcanAccess
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>Belum ada misi.</p>
            @endif
        </div>
    </div>
</div>

<!-- Add Vision Modal -->
<div class="modal fade" id="addVisionModal" tabindex="-1" role="dialog" aria-labelledby="addVisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVisionModalLabel">Tambah Visi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('vision.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="vision">Visi</label>
                        <input type="text" class="form-control" id="vision" name="vision" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Vision Modal -->
@if($vision)
<div class="modal fade" id="editVisionModal" tabindex="-1" role="dialog" aria-labelledby="editVisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVisionModalLabel">Edit Visi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('vision.update', @@$vision->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="vision">Visi</label>
                        <input type="text" class="form-control" id="vision" name="vision" value="{{ @$vision->vision }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Add Mission Modal -->
<div class="modal fade" id="addMissionModal" tabindex="-1" role="dialog" aria-labelledby="addMissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMissionModalLabel">Tambah Misi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('mission.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="mission">Misi</label>
                        <input type="text" class="form-control" id="mission" name="mission" required>
                        <input type="hidden" value="{{  @$vision->id }}" name="vision">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Mission Modals -->
@if($vision && @$vision->missions->count())
@foreach(@$vision->missions as $mission)
<div class="modal fade" id="editMissionModal{{ $mission->id }}" tabindex="-1" role="dialog" aria-labelledby="editMissionModalLabel{{ $mission->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMissionModalLabel{{ $mission->id }}">Edit Misi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('mission.update', $mission->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="mission">Misi</label>
                        <input type="text" class="form-control" id="mission" name="mission" value="{{ $mission->mission }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif

@endsection

@section('css')
    <style>
        .modal .modal-header {
            background-color: #007bff;
            color: #fff;
        }
        .modal .modal-header .close {
            color: #fff;
        }
        .modal-body {
            padding: 2rem;
        }
        .modal-content {
            border-radius: 0.5rem;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('.modal').on('shown.bs.modal', function() {
                $(this).find('input:first').focus();
            });
        });
    </script>
@endsection
