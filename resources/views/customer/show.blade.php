@extends('adminlte::page')

@section('title', 'Detail Customer')

@section('content')
<div class="container mt-4">
    @if(session('store'))
        <div class="alert alert-success">
            Data customer berhasil disimpan.
        </div>
    @endif
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Detail Customer</h3>
            <a href="{{ route('customer.index') }}" class="btn btn-warning">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Informasi Umum</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th>Nama</th>
                            <td>{{ $customer->name }}</td>
                        </tr>
                        <tr>
                            <th>Direktur</th>
                            <td>{{ $customer->director }}</td>
                        </tr>
                        <tr>
                            <th>PIC</th>
                            <td>{{ $customer->pic }}</td>
                        </tr>
                        <tr>
                            <th>Pemberi Tugas</th>
                            <td>{{ $customer->assignor }}</td>
                        </tr>
                        <tr>
                            <th>Industri</th>
                            <td>{{ $customer->industry }}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h5>Kontak & Lokasi</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $customer->address }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $customer->phone }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $customer->email }}</td>
                        </tr>
                        <tr>
                            <th>Kota</th>
                            <td>{{ $customer->city }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-warning mr-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('customer.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus data ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Quotes Section -->
    <div class="card">
        <div class="card-header">
            <h3>Quotes</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Quote Number</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quote as $a)
                            <tr>
                                <td>{{ $a->number_result }}</td>
                                <td>{{ $a->date }}</td>
                                <td>{{ $a->total ? "Rp. ". number_format($a->total,0,',','.') : "Rp. 0" }}</td>
                                <td>
                                    @if($a->status == "Open")
                                    <span class='badge badge badge-success'>Open</span>
                                    @else
                                    <span class='badge badge badge-danger'>Closed</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('quote.download.pdf', $a->slug) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i> Show
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination Links for Quotes -->
                <div class="d-flex justify-content-center">
                    {{ $quote->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Work Orders</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Work Order Number</th>
                            <th>Quote Number</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($workOrders as $workOrder)
                            <tr>
                                <td>{{ $workOrder->number_result }}</td>
                                <td>{{ $workOrder->quote ? $workOrder->quote->number_result : ""}}</td>
                                <td>{{ $workOrder->total ? "Rp. ". number_format($workOrder->total,0,',','.') : "Rp. 0" }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination Links for Work Orders -->
                <div class="d-flex justify-content-center">
                    {{ $workOrders->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Work Orders Section -->
</div>
@endsection

@section('css')
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .table-borderless th {
        width: 150px;
        font-weight: bold;
    }
    .btn-warning, .btn-danger {
        border-radius: 20px;
    }
    .thead-light th {
        background-color: #f8f9fa;
    }
</style>
@endsection