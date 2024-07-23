@extends('adminlte::page')

@section('title', 'Pengajuan Anggaran')

@section('content_header')
    <h1>Pengajuan Anggaran</h1>
@endsection

@section('content')
<div class="col-md-12 mt-2">
    @if(Session::get('store'))
        <div class="alert alert-success mt-3">Anggaran Divisi Berhasil Dibuat</div>
    @endif
    @if(Session::get('update'))
        <div class="alert alert-success mt-3">Anggaran Divisi Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
        <div class="alert alert-success mt-3">Anggaran Divisi Berhasil Dihapus</div>
    @endif
    @if(Session::get('approve'))
        <div class="alert alert-success mt-3">Anggaran Divisi Berhasil Disetujui</div>
    @endif
    @if(Session::get('reject'))
        <div class="alert alert-danger mt-3">Anggaran Divisi Berhasil Tidak Disetujui</div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        @canAccess('create','division_budgets')
        <a href="{{ route('division-budget.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Anggaran</a>
        @endcanAccess
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="col-2">Name</th>
                    <th class="col-1">Divisi</th>
                    <th class="col-2">Anggaran</th>
                    <th class="col-2">Sisa Anggaran</th>
                    <th class="col-1">Presentase Penyerapan</th>
                    <th class="col-1">Status</th>
                    <th class="col-2">Aksi</th>
                    <th class="col-2">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($divisionBudgets as $budget)
                <tr>
                    <td>{{ $budget->name }}</td>
                    <td>{{ $budget->division->name }}</td>
                    <td>{{ 'Rp. '.number_format($budget->initial_budget,0,',','.') }}</td>
                    <td>{{ 'Rp. '.number_format($budget->amount,0,',','.') }}</td>
                    <td>{{ $budget->budget_usage_percentage }}%</td>
                    <td>
                        @if(is_null($budget->is_approved))
                            @if($approval)
                                @canAccess('approve','division_budgets')
                                <form action="{{ route('division-budget.approve', $budget->slug) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <input type="hidden" name="status" value="1">
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-check"></i></button>
                                </form>
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#approvalModal" data-status="0" data-id="{{ $budget->slug }}">
                                    <i class="fa fa-times"></i>
                                </button>
                                @endcanAccess
                            @else
                                <span class="badge badge-warning">Waiting</span>
                            @endif
                        @elseif($budget->is_approved)
                            <span class="badge badge-success">Approved</span>
                        @else
                            <span class="badge badge-danger">Declined</span>
                        @endif
                    </td>
                    <td>
                        @if(is_null($budget->is_approved))
                        @canAccess('edit','division_budgets')
                        <a href="{{ route('division-budget.edit', $budget->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                        @endcanAccess

                        @canAccess('show','division_budgets')
                        <a href="{{ route('division-budget.show', $budget->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                        @endcanAccess
                        
                        @canAccess('destroy','division_budgets')
                        <form action="{{ route('division-budget.destroy', $budget->slug) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                        </form>
                        @endcanAccess
                        
                        @elseif($budget->is_approved == 0)
                        @canAccess('edit','division_budgets')
                        <a href="{{ route('division-budget.edit', $budget->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                        @else
                        @canAccess('show','division_budgets')
                        <a href="{{ route('division-budget.show', $budget->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                        @endcanAccess
                        @endif
                    </td>
                    <td>
                        @if($budget->notes)
                            {{ $budget->notes }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $divisionBudgets->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" id="approvalForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalLabel">Catatan Penolakan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="notes">Catatan</label>
                        <input name="notes" id="notes" class="form-control" required>
                    </div>
                    <input type="hidden" name="status" id="approvalStatus">
                    <input type="hidden" name="id" id="budgetId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#approvalModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var status = button.data('status');
            var slug = button.data('id');
            var modal = $(this);

            modal.find('#approvalStatus').val(status);
            modal.find('#budgetId').val(slug);
            modal.find('#approvalForm').attr('action', '/division-budget/approve/' + slug);
        });
    });
</script>
@endsection

@section('css')
<style>
    .card-header {
        font-weight: bold;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .form-label {
        font-weight: bold;
    }
    .modal-header {
        background-color: #007bff;
        color: white;
    }
    .modal-footer {
        background-color: #f1f1f1;
    }
    .badge-warning {
        background-color: #ffc107;
    }
    .badge-success {
        background-color: #28a745;
    }
    .badge-danger {
        background-color: #dc3545;
    }
</style>
@endsection
