@extends('adminlte::page')

@section('title', 'Pengajuan Surat')

@section('content')
@if(session('success'))
    <div class="alert alert-success mt-2">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="card mt-3">
    <div class="card-header">
        <h3>Pengajuan Surat</h3>
    </div>
    <div class="card-body">
        @canAccess('create','letter_submissions')
        <a href="{{ route('letter-submission.create') }}" class="btn btn-primary float-right mb-3"><i class="fa fa-plus"></i> Pengajuan Surat</a>
        @endcanAccess
        <form method="GET" action="{{ route('letter-submission.index') }}" class="mb-3">
            <div class="row align-items-end gy-2">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <select class="form-control select2" id="letterType" name="letterType">
                            <option value="">-- Select Letter Type -- </option>
                            @foreach ($letterTypes as $letterType)
                                <option value="{{ $letterType->id }}" {{ request('letterType') == $letterType->id ? 'selected' : '' }}>{{ $letterType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <select class="form-control select2" name="user">
                            <option value="">-- User -- </option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->name ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <div class="form-group">
                        @php
                            $order = request('sort', 'desc');
                        @endphp
                            <select name="sort" class="form-control">
                                <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Created By</option>
                                <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Created By</option>
                            </select>
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                            <button type="button" onclick="window.location.href='{{ route('letter-submission.index') }}'" class="btn btn-secondary"><i class="fa fa-times"></i> Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <form action="{{ route('letter-submission.approvement') }}" method="POST" id="bulk-action-form">
            @csrf
            @canAccess('approvement','letter_submissions')
            @method('PATCH')
            <div class="mb-3">
                <button type="submit" class="btn btn-success" name="action" value="approve">Approve</button>
            </div>
            @endcanAccess
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @canAccess('approvement','letter_submissions')
                            <th><input type="checkbox" id="select-all"></th>
                            @endcanAccess
                            <th>Nama Lengkap</th>
                            <th>Surat</th>
                            <th>Status</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Aksi</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($letterSubmissions as $submission)
                        <tr id="submission-{{ $submission->id }}">
                            @canAccess('approvement','letter_submissions')
                            <td>
                                @if($submission->is_approved === null)
                                    @if(is_null($submission->status) || $submission->status == 1)
                                        <input type="checkbox" name="selected_ids[]" value="{{ $submission->id }}">
                                    @else
                                        <i class="fa fa-times"></i>
                                    @endif
                                @else
                                    @if($submission->is_approved)
                                        <i class="fa fa-check"></i>
                                    @else
                                        <i class="fa fa-times"></i>
                                    @endif
                                @endif
                            </td>
                            @endcanAccess
                            <td>{{ $submission->user->name ?? '-' }}
                                @if($submission->createdBy)
                                <br>
                                <small>
                                   <i class="fa fa-user"></i>  Dibuat oleh {{ $submission->createdBy ? $submission->createdBy->name : '' }}
                                </small>
                                @endif
                            </td>
                            <td>{{ $submission->letterType->name ?? '-' }}</td>
                            <td>
                                @if($submission->is_approved === null)
                                    @if($submission->status == 0 && isset($submission->status))
                                        <span class="badge bg-info">Need Signatur</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                @elseif($submission->is_approved)
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>{{ $submission->created_at->format('d-m-Y') }}</td>
                            <td>
                                @canAccess('show','letter_submissions')
                                <a href="{{ route('letter-submission.show', $submission) }}" target="_blank" class="btn btn-info btn-sm"><i class="fa fa-file-pdf"></i></a>
                                @endcanAccess
                                @canAccess('edit','letter_submissions')
                                @if($submission->is_editable)
                                <a href="{{ route('letter-submission.edit', $submission) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                @endif
                                @endcanAccess
                                
                                @canAccess('destroy','letter_submissions')
                                @if(!isset($submission->status))
                                <button type="button" class="btn btn-danger btn-sm delete-submission" data-id="{{ $submission->id }}"><i class="fa fa-trash"></i></button>
                                @endif
                                @endcanAccess
                            </td>
                            <td>
                                {{ $submission->reason ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{ $letterSubmissions->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </form>
    </div>

</div>
@endsection
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        // Initialize Select2
        $('.select2').select2();
        // $('.select2mainProject').select2();
        // $('.UserSelect2').select2();
    });
</script>
@canAccess('approvement','letter_submissions')
<script>
    // Select/Deselect all checkboxes
    document.getElementById('select-all').addEventListener('click', function() {
        let checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
</script>
@endcanAccess
@canAccess('destroy','letter_submissions')
<script>
    // Delete letter submission with confirmation using SweetAlert and AJAX
    document.querySelectorAll('.delete-submission').forEach(button => {
        button.addEventListener('click', function() {
            const submissionId = this.getAttribute('data-id');
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Pengajuan ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete
                    let url = `{{ route('letter-submission.destroy', ':id') }}`;
                    url = url.replace(':id', submissionId);
                    
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Dihapus!',
                                    'Pengajuan berhasil dihapus.',
                                    'success'
                                );
                                // Remove row from table
                                document.getElementById(`submission-${submissionId}`).remove();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Penghapusan gagal.',
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat menghapus.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });
</script>
@endcanAccess
@endsection
