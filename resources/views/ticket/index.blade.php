
@extends('adminlte::page')

@section('content')
<div class="container py-3">
<div class="col-md-12">
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Tiket Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Tiket Berhasil Terhapus</div>
    @endif

</div>
    <div class="card">
        <div class="card-body">
            <h2 class="mb-4">Daftar Tiket</h2>
            <form action="{{ route('ticket.index') }}" method="get">
                <div class="d-flex flex-row-reverse">
                    <div class="p-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="p-2">
                        <input type="text" name="name" class="form-control" placeholder="Search">
                    </div>
                    <div class="p-2">
                    @php
                        $order = request('order', 'desc');
                    @endphp
                        <select name="order" class="form-control">
                            <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Created By</option>
                            <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Created By</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="table-responsive-md">
                <table class="table table-borderles">
                    <thead>
                        <tr>
                            <th class="col-2">Tanggal</th> <!-- Smaller width for date -->
                            <th class="col-1">Status</th> <!-- Smaller width for status -->
                            <th class="col-2">Subjek</th> <!-- Reasonable width for subject -->
                            <th class="col-5">Isi</th> <!-- Larger width for content -->
                            <th class="col-2">Aksi</th> <!-- Width for actions -->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d-m-Y') }}</td>
                                <td>{{ ucfirst($ticket->status) }}</td>
                                <td>{{ $ticket->subject }}</td>
                                <td>{!! $ticket->content !!}</td>
                                <td>
                                    <form action="{{ route('ticket.destroy', $ticket->slug) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        @canAccess('show','tickets')
                                        <a href="{{ route('ticket.show', $ticket->slug) }}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a>
                                        @endcanAccess
                                        @canAccess('destroy','tickets')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                        @endcanAccess
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No tickets available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $tickets->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

@stop
@section('css')
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
    </style>
@stop
