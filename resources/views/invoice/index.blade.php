@extends('adminlte::page')

@section('content')
<div class="container">
    <h1>Invoice List</h1>

    <!-- Button to trigger create modal -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
        Create Invoice
    </button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>BAST ID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->id }}</td>
                <td>{{ $invoice->user->name }}</td>
                <td>{{ $invoice->bast_id }}</td>
                <td>{{ $invoice->start_date }}</td>
                <td>{{ $invoice->end_date }}</td>
                <td>{{ $invoice->status }}</td>
                <td>
                    <!-- Button to trigger edit modal -->
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editInvoiceModal" 
                            data-id="{{ $invoice->id }}" data-status="{{ $invoice->status }}">
                        Edit
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createInvoiceModalLabel">Create New Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('invoice.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="bast_id" class="form-label">BAST ID</label>
                        <select name="bast" class="form-control select2" id="">
                            @foreach($bast as $a)
                            <option value="{{ $a->id }}">{{ $a->number_result }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" name="status" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInvoiceModalLabel">Edit Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_invoice_id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" name="status" id="edit_status" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-warning">Update Invoice</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script>
    var editModal = document.getElementById('editInvoiceModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var status = button.getAttribute('data-status');

        var modalTitle = editModal.querySelector('.modal-title');
        var invoiceIdInput = editModal.querySelector('#edit_invoice_id');
        var statusInput = editModal.querySelector('#edit_status');

        modalTitle.textContent = 'Edit Invoice #' + id;
        invoiceIdInput.value = id;
        statusInput.value = status;

        document.getElementById('editInvoiceForm').action = '/invoices/' + id;
    });
</script>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
@stop
