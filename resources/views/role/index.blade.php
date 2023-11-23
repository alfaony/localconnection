@extends('adminlte::page')

{{-- @section('title', 'User') --}}

@section('content_header')
    <h1>Role List  </h1>
@stop

@section('content')


@php
$no =1;
$heads = [
    'No',
    'Nama',
    'Divisi',
    'Posisi'
];


@endphp
<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="row" style="margin-bottom: 10px">
                    <div class="col-md-12">
                        <a href="{{ route('role.create') }}" class="btn btn-success col-xs-12 btn  btn-primary btn-gap"> <li class="fa fa-plus"></li> <span title="Buat Data Baru">Create</span></a>                          
                    </div>
            </div>
            <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
                @endif
                <table class="table table-bordered">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th width>Action</th>
                    </tr>

                    @forelse($role as $a)
                    <tr>

                        <td>
                            {{ $no++ }}
                        </td>
                        <td>
                            {{ $a->name }}
                        </td>
                        <td>
                            <form action="{{ route('role.destroy',$a) }}" method="post">
                            @csrf
                                <input type="hidden" name="_method" value="DELETE">
                            <a class="btn btn-xs btn-default text-teal mx-1 shadow" href="{{ route('role.show',$a) }}" > <i class="fa fa-lg fa-fw fa-eye"></i></a>
                            <a class="btn btn-xs btn-default text-primary mx-1 shadow" href="{{ route('role.edit',$a) }}" > <i class="fa fa-lg fa-fw fa-pen"></i></a>
                                <button class="btn btn-xs btn-default text-danger mx-1 shadow"  onclick="return window.confirm('{{ __('Are you sure') }}?')" ><i class="fa fa-lg fa-fw fa-trash"></i></button>
                            </form>
                        </td>
                        </tr>
                        @empty
                        <tr>
                            <td>
                                Data Kosong
                            </td>
                        </tr>
                    @endforelse
                </table>
                </div>
            </div>
        </div>
    </div>
</div>    
@stop