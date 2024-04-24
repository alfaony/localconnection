@extends('adminlte::page')

@section('content')
<div class="container p-3 mt-3">
    <div class="card">
        <div class="card-header">
            <h4>Detail Tugas: {{ $taskAssign->task->name }}</h4>
        </div>
        <div class="card-body">
            <!-- Informasi Tugas -->
            <div class="row mb-3">
                <div class="col-12 col-md-6">
                    <h5>Informasi Tugas</h5>
                    <p><strong>Tanggal Penugasan:</strong> {{ $taskAssign->date }}</p>
                    <p><strong>Status Tugas:</strong> @switch($taskAssign->taskStatus->name)
                            @case('doing')
                                <i class="fa fa-hourglass-start"></i> Doing
                                @break
                            @case('in review')
                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                @break
                            @case('not complete')
                                <i class="fa fa-times-circle" style="color: red;"></i> Not Complete
                                @break
                            @case('complete')
                                <i class="fa fa-check" style="color: green;"></i> Complete
                                @break
                            @default
                                {{ $taskAssign->taskStatus->name }}
                        @endswitch
                    </p>
                    <p><strong>Penugasan Kepada:</strong> {{ $taskAssign->assign->name }}</p>
                    <p><strong>Tugas Dibuat:</strong> {{ \Carbon\Carbon::parse($taskAssign->created_at)->format('H:i:s') }}</p>
                    <p><strong>Tugas Diperbarui:</strong> {{ \Carbon\Carbon::parse($taskAssign->updated_at)->format('H:i:s')  }}</p>
                </div>
                <!-- Formulir untuk Foto dan Catatan -->
                <div class="col-12 col-md-6">
                    <h5>Laporan Tugas</h5>
                    @if($taskAssign->taskReport)
                        <!-- Menampilkan laporan yang sudah ada -->
                        <div>
                            <img src="{{ Storage::url('task/' .$taskAssign->taskReport->picture) }}" class="img-fluid mb-2" alt="Foto Laporan">
                            <p><strong>Catatan:</strong> {{ $taskAssign->taskReport->note }}</p>
                        </div>
                    @else
                    @canAccess('report','task_assigns')
                    <form action="{{ route('task-assign.report', $taskAssign->slug) }}" method="POST" enctype="multipart/form-data" id="captureForm">
                        @csrf
                        @method('put')
                        <div class="mb-3">
                            <label for="photo" class="form-label">Ambil Foto</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*" capture="environment" onchange="compressAndPreviewImage();">
                            <small class="text-muted">Klik untuk mengambil foto menggunakan kamera.</small>
                            <img id="photo-preview" src="#" alt="Photo Preview" style="display:none;" class="img-fluid mt-3"/>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Catatan</label>
                            <textarea class="form-control" id="note" name="note" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                    @endcanAccess
                    @endif
                </div>
            </div>
            <!-- Navigasi -->
            <div class="d-flex justify-content-between mt-4">
                @canAccess('edit','task_assigns')
                <a href="{{ route('task-assign.edit', $taskAssign->slug) }}" class="btn btn-info"><i class="fa fa-edit"></i> Edit</a>
                @endcanAccess
                <a href="{{ route('task-assign.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Daftar Tugas</a>
                @if($taskAssign->taskReport && $taskAssign->taskStatus->name == "in review")
                @canAccess('approvement','task_assigns')
                <form action="{{ route('task-assign.approvement', $taskAssign->slug) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="complete">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Complete</button>
                </form>
                <form action="{{ route('task-assign.approvement', $taskAssign->slug) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="not complete">
                    <button type="submit" class="btn btn-warning"><i class="fa fa-times"></i> Incomplete</button>
                </form>
                @endcanAccess
                @endif
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
            const MAX_WIDTH = 800;

            const scaleSize = MAX_WIDTH / e.target.width;
            canvas.width = MAX_WIDTH;
            canvas.height = e.target.height * scaleSize;

            const ctx = canvas.getContext("2d");
            ctx.drawImage(e.target, 0, 0, canvas.width, canvas.height);
            ctx.canvas.toBlob((blob) => {
                const file = new File([blob], "filename.jpg", {
                    type: 'image/jpeg',
                    quality: 0.8
                });

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function () {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                }
            }, 'image/jpeg', 0.8);
        }
    }
}
</script>
@endsection
