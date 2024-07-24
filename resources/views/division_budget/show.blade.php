@extends('adminlte::page')

@section('title', 'Detail Anggaran Divisi')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('division-budget.index') }}">Pengajuan Anggaran</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $divisionBudget->name ?? '' }}</li>
    </ol>
</nav>
<div class="col-md-12 mt-2">
    @if(Session::get('delete'))
        <div class="alert alert-success mt-3">File Berhasil Dihapus</div>
    @endif
</div>
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">{{ $divisionBudget->name }}</h3>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Divisi:</strong> {{ $divisionBudget->division->name }}</p>
                <p><strong>Nama Anggaran:</strong> {{ $divisionBudget->name }}</p>
                <p><strong>Persentase Penyerapan:</strong> {{ $divisionBudget->budget_usage_percentage }}%</p>
            </div>
            <div class="col-md-6">
                <p><strong>Anggaran Awal:</strong> Rp {{ number_format($divisionBudget->initial_budget, 0, ',', '.') }}</p>
                <p><strong>Sisa Anggaran:</strong> Rp {{ number_format($divisionBudget->amount, 0, ',', '.') }}</p>
            </div>
            <div class="col-md-12">
                <h6><Strong>Deskripsi</Strong></h6>
                {!! $divisionBudget->description !!}
            </div>
        </div>

        <h4 class="mb-3">Quotes</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No Quote</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($divisionBudget->quotes as $quote)
                    <tr>
                        <td>{{ $quote->number_result }}</td>
                        <td>Rp {{ number_format($quote->total, 0, ',', '.') }}</td>
                        <td>
                            <a target="_blank" href="{{ route('quote.download.pdf', $quote->slug) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> Lihat Quote
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($divisionBudget->quotes->isEmpty())
        <div class="alert alert-info mt-3">
            Tidak ada quotes yang terkait dengan anggaran ini.
        </div>
        @endif

        <h4 class="mb-3 mt-4">File Pendukung</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nama File</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($divisionBudget->file)
                        @foreach (json_decode($divisionBudget->file) as $file)
                            <tr>
                                <td>
                                    <i class="fa fa-file"></i>
                                    {{ basename($file) }}
                                </td>
                                <td>
                                    <form action="{{ route('division-budget.destroy', $divisionBudget->slug) }}" method="POST" style="display:inline-block;">
                                        <a target="_blank" href="{{ Storage::url($file) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-eye"></i> Lihat
                                        </a>
                                        <input type="hidden" name="action" value="removeFile">
                                        <input type="hidden" name="file" value="{{ $file }}">
                                        @canAccess('destroy','division_budgets')
                                        @csrf
                                        @method('DELETE')
                                        @if($file && $divisionBudget->is_approved != 1)
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                        @endif
                                        @endcanAccess
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" class="text-center">Tidak ada file pendukung.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .card {
        border-radius: 10px;
    }

    .table-responsive {
        margin-top: 20px;
    }
    .card-header {
        border-radius: 10px 10px 0 0;
    }
    .alert-info {
        border-radius: 10px;
    }
</style>
@stop

@push('js')
<script>
    document.querySelectorAll('.delete-file-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!window.confirm('Apakah Anda yakin ingin menghapus file ini?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush