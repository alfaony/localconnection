@extends('adminlte::page')

@section('content_header')
    <h1>Device Management</h1>
@stop

@section('content')
<div class="row justify-content-end align-items-center mb-4">
    <div class="col-auto">
        <div class="input-group">
            <input type="search" class="form-control form-control" placeholder="Search devices" aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h3 class="card-title">Device List</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="datatableDevices">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code/Type</th>
                    <th>Location/Type</th>
                    <th>Connectivity</th>
                    <th>Status (ON/OFF)</th>
                    <th>Availability</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data rows will be inserted here by DataTables -->
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="/css/custom.css">
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .status-dot {
        height: 15px;
        width: 15px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-active { background-color: #4CAF50; }
    .status-inactive { background-color: #f44336; }
</style>
@stop

@section('js')

@stop
