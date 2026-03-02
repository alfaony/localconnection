@extends('adminlte::page')
@section('content')
<div class="container mt-3">
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

    <div class="card">
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-6">
                    <h2>Laporan Proyek</h2>
                    <div class="mt-5">No Report: {{ $nomorReportProject ?? '' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                        <div class="col-sm-4">
                            <input type="date" name="date" class="form-control" id="date" value="{{ old('date') ?? @$reportProject->date }}" disabled>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <label class="col-sm-8 col-form-label text-right">PM:</label>
                        <div class="col-sm-4">
                            <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label>Pilih Data Proyek</label>
                    <select class="form-control select2" name="project" id="" disabled>
                        @foreach($project as $a)
                        <option value="{{ $a->id }}" {{ @$reportProject->project_id == $a->id ? 'selected'  : ''}} {{ @$selectedProject->id == $a->id ? 'selected' : '' }}>
                            {{ $a->title }} {{ $a->workOrder->number_result }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered mt-3" id="tableReport">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Laporan</th>
                            <th>Link</th>
                            <th>File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(@$reportProject)
                        @php $no = 1; @endphp
                        @foreach(@$reportProject->reportProjectDetail as $index => $a)
                        <tr>
                            <td width="5%">{{ $no++ }}</td>
                            <td width="auto">
                                <input type="text" class="form-control" value="{{ $a->name }}" readonly>
                            </td>
                            <td width="5%">
                                @if($a->is_report)
                                <i class="fa fa-check"></i>
                                @else
                                <i class="fa fa-times"></i>
                                @endif
                            </td>
                            <td width="30%">
                                <a href="{{ $a->link }}" target="_blank"> Report Link</a>
                            </td>
                            <td>
                                <a href="{{ s3_asset(true,10,'reports/' . $a->file) }}" class="btn btn-sm btn-primary" download title="{{ $a->file }}"><i class="fa fa-download"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Approvement Buttons -->
            <div class="mt-4">
                @if(isset($reportProject->is_approve))
                    @if($reportProject->is_approve)
                        <span class="badge badge-success">Approved</span>
                    @else
                    <span class="badge badge-danger">Rejected</span>
                    <p><b>Reason:</b> {{ $reportProject->note }}</p>
                    @endif
                @else
                    @canAccess('approvement', 'report_projects')
                    <button class="btn btn-success" id="btnApproveAll">Approve</button>
                    <button class="btn btn-danger" id="btnRejectAll">Reject</button>
                    @endcanAccess
                @endif
            </div>
            <div class="mt-4">
                @canAccess('edit', 'report_projects')
                <a href="{{ route('report-project.edit', $reportProject->slug) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
                @endcanAccess
            </div>
        </div>
    </div>
</div>

<!-- Modal for Approvement -->
<div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalLabel">Catatan Penolakan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" id="approvalNote" rows="4" placeholder="Tambahkan catatan..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitNote">Submit</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
@canAccess('approvement', 'report_projects')
<script>
    $(document).ready(function () {
        var isApprove;

        // Handle Approve All
        $('#btnApproveAll').click(function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitApproval(1, '');
                }
            });
        });

        // Handle Reject All
        $('#btnRejectAll').click(function() {
            isApprove = 0;

            // Show modal for adding notes
            $('#noteModal').modal('show');
        });

        // Handle Submit Note
        $('#btnSubmitNote').click(function() {
            var note = $('#approvalNote').val(); // Get the note from modal
            if (isApprove === 0 && note.trim() === '') {
                Swal.fire('Error!', 'Please add a reason for rejection.', 'error');
                return;
            }

            submitApproval(isApprove, note);
        });

        // Function to submit approval
        function submitApproval(isApprove, note) {
            var projectId = "{{ @$reportProject->id }}"; // Get the project ID
            let url = "{{ route('report-project.approvement', ':id') }}";
            url = url.replace(':id', projectId);

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    _method: "PUT",
                    _token: "{{ csrf_token() }}",
                    is_approve: isApprove,
                    note: note
                },
                success: function(response) {
                    $('#noteModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 1500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) 
                {
                    console.log(xhr);
                }
            });
        }
    });
</script>
@endcanAccess
@stop

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .badge-success { background-color: #28a745; }
    .badge-danger { background-color: #dc3545; }
</style>
@stop