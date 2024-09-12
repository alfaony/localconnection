@extends('adminlte::page')

@section('title', 'Pengajuan Surat')

@section('content')
@if(session('success'))
    <div class="alert alert-success mt-2">
        {{ session('success') }}
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
        <form action="{{ route('letter-submission.approvement') }}" method="POST" id="bulk-action-form">
            @csrf
            @canAccess('approvement','letter_submissions')
            @method('PATCH')
            <div class="mb-3">
                <button type="submit" class="btn btn-success" name="action" value="approve">Approve</button>
                <button type="submit" class="btn btn-danger" name="action" value="decline">Decline</button>
            </div>
            @endcanAccess

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
                    </tr>
                </thead>
                <tbody>
                    @foreach($letterSubmissions as $submission)
                    <tr id="submission-{{ $submission->id }}">
                        @canAccess('approvement','letter_submissions')
                        <td>
                            @if($submission->is_approved === null)
                            <input type="checkbox" name="selected_ids[]" value="{{ $submission->id }}">
                            @else
                            <i class="fa fa-check"></i>
                            @endif
                        </td>
                        @endcanAccess
                        <td>{{ $submission->user->name ?? '-' }}</td>
                        <td>{{ $submission->letterType->name ?? '-' }}</td>
                        <td>
                            @if($submission->is_approved === null)
                                <span class="badge bg-warning">Pending</span>
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
                            <a href="{{ route('letter-submission.edit', $submission) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                            @endcanAccess
                            
                            @canAccess('destroy','letter_submissions')
                            @if($submission->status === 0)
                            <button type="button" class="btn btn-danger btn-sm delete-submission" data-id="{{ $submission->id }}"><i class="fa fa-trash"></i></button>
                            @endif
                            @endcanAccess
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            {{ $letterSubmissions->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
