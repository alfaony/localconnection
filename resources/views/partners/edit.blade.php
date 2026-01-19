@extends('adminlte::page')

@section('title', 'Edit Partner')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit Partner: {{ $partner->name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Partners</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.show', $partner) }}">{{ $partner->name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Partner Information</h3>
                </div>
                <form action="{{ route('partner.update', $partner) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @include('partners.form')
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Partner
                        </button>
                        <a href="{{ route('partner.show', $partner) }}" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Partner Information</h3>
                </div>
                <div class="card-body">
                    <p class="small">
                        <strong>Created:</strong><br>
                        {{ $partner->created_at->format('d M Y H:i') }}
                    </p>
                    <p class="small">
                        <strong>Last Updated:</strong><br>
                        {{ $partner->updated_at->format('d M Y H:i') }}
                    </p>
                    <p class="small">
                        <strong>Total Targets:</strong><br>
                        {{ $partner->targets->count() }} year(s)
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop