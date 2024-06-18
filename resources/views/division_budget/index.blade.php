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
    @if(Session::get('approve'))
        <div class="alert alert-success mt-3">Anggaran Divisi Berhasil Tidak Disetujui</div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        @canAccess('create','division_budgets')
        <a href="{{ route('division-budget.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Anggaran</a>
        @endcanAccess
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="col-2">Name</th>
                    <th class="col-1">Divisi</th>
                    <th class="col-3">Anggaran</th>
                    <th class="col-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($divisionBudgets as $budget)
                <tr>
                    <td>{{ $budget->name }}</td>
                    <td>{{ $budget->division->name }}</td>
                    <td>{{ 'Rp.'.number_format($budget->amount,0,',','.') }}</td>
                    <td>
                        @if(!isset($budget->is_approved))
                        @canAccess('edit','division_budgets')
                        <a href="{{ route('division-budget.edit', $budget->slug) }}" class="btn btn-warning"><i class="fa fa-edit"></i></a>
                        @endcanAccess

                        @canAccess('destroy','division_budgets')
                        <form action="{{ route('division-budget.destroy', $budget->slug) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                        </form>
                        @endcanAccess

                        @canAccess('approve','division_budgets')
                        <form action="{{ route('division-budget.approve', $budget->slug) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <input type="hidden" name="status" value="1">
                            <button type="submit" class="btn btn-success"><i class="fa fa-check"></i></button>
                        </form>

                        <form action="{{ route('division-budget.approve', $budget->slug) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <input type="hidden" name="status" value="0">
                            <button type="submit" class="btn btn-danger"><i class="fa fa-times"></i></button>
                        </form>
                        @endcanAccess

                        @elseif($budget->is_approved == 0)
                        <button type="button" class="btn btn-danger"><i class="fa fa-times"></i></button>
                        
                        @canAccess('edit','division_budgets')
                        <a href="{{ route('division-budget.edit', $budget->slug) }}" class="btn btn-warning"><i class="fa fa-edit"></i></a>
                        @endcanAccess

                        @else
                        <button type="button" class="btn btn-success"><i class="fa fa-check"></i></button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $divisionBudgets->links() }}
    </div>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('amount');
        amountInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/,/g, '');
            e.target.value = formatNumber(value);
        });

        function formatNumber(value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    });
</script>
@endsection
