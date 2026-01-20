@extends('adminlte::page')

@section('title', 'Create Partner')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Create New Partner</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Partners</a></li>
                <li class="breadcrumb-item active">Create</li>
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
                <form action="{{ route('partner.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @include('partners.form', ['partner' => null])
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Partner
                        </button>
                        <a href="{{ route('partner.index') }}" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Information</h3>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        <strong>Company & PIC:</strong><br>
                        The company is automatically set to your current company. Select a PIC (Person In Charge) from the dropdown list of users in your company.
                    </p>
                    <p class="small text-muted">
                        <strong>Partner Type:</strong><br>
                        Select the type of partnership relationship.
                    </p>
                    <p class="small text-muted">
                        <strong>Certification:</strong><br>
                        Mark as certified if the partner has obtained official certification.
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop