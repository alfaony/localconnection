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
<div class="box box-danger">
    <div class="box-body">
        <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                    <div class="row">
                        <div class="container mt-5">
                            @if(@$role)
                            <form method="post" action="{{ route('role.update',$role) }}">
                            @method('PATCH')
                            @else
                            <form method="post" action="{{ route('role.store') }}">
                            @endif
                                @csrf
                                <div class="row">
                                    <div class="container mt-5">
                                        <div class="card">
                                            <div class="card-header">
                                                @if(@$is_editable)
                                                <x-adminlte-input name="name" label="Role Name" value="{{ @$role->name}}" class="form-control" />
                                                @else
                                                <x-adminlte-input name="name" label="Role Name" value="{{ @$role->name}}" class="form-control" disabled/>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="accordion">
                                    <!-- Accordion 2 -->
                                    <div class="card">
                                        <div class="card-header">
                                            <a class="collapsed card-link" data-toggle="collapse" href="#menu2">
                                                Menu Utama
                                            </a>
                                        </div>
                                        <div id="menu2" class="collapse show" data-parent="#accordion">
                                            <div class="card-body">
                                                <div class="row">
                                                @foreach($mainMenus as $a)

                                                    <!-- Card pertama -->
                                                    <div class="col-md-6">
                                                        <div id="accordion-{{ $a }}">
                                                            <div class="card">
                                                                <div class="card-header" id="heading-{{ $a }}">
                                                                    <h5 class="mb-0">
                                                                        <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapse-{{ $a }}" aria-expanded="true" aria-controls="collapse-{{ $a }}">
                                                                            {{ $a }}
                                                                        </button>
                                                                    </h5>
                                                                </div>

                                                                <div id="collapse-{{ $a }}" class="collapse" aria-labelledby="heading-{{ $a }}" data-parent="#accordion-{{ $a }}">
                                                                    <div class="card-body">
                                                                        @foreach($dataPermission[$a] as $value)
                                                                            <div class="form-group">
                                                                                <div class="form-check">
                                                                                    @if(@$is_editable)
                                                                                        <input class="form-check-input" type="checkbox" name="permission[]" value="{{ $value['id'] }}" {{ @$rolePermissions[$value['id']] ? "CHECKED" : ""}} >
                                                                                    @else
                                                                                        <input disabled class="form-check-input" type="checkbox" name="permission[]" value="{{ $value['id']}}" {{ @$rolePermissions[$value['id']] ? "CHECKED" : ""}} >
                                                                                    @endif
                                                                                    <label class="form-check-label">
                                                                                        {{ $value['name'] }}
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary">{{__('submit')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
@stop
