{{-- create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Create PPP Profile')

@section('content_header')
    <h1>Create PPP Profile</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Profile Information</h3>
    </div>
    
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('mikrotik-profile.store') }}">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" name="name" id="name" required value="{{ old('name') }}">
            </div>
            
            <div class="form-group">
                <label for="rate_limit">Rate Limit (Mikrotik-Rate-Limit)</label>
                <input type="text" class="form-control" name="rate_limit" id="rate_limit" 
                       placeholder="ex: 20M/20M 20M/20M 20M/20M 1/1" value="{{ old('rate_limit') }}">
                <small class="form-text text-muted">Format klasik: rx/tx (bisa up to 4 token untuk burst)</small>
            </div>
            
            <div class="form-group">
                <label for="remote_address">Remote Address (Pool/IP)</label>
                <input type="text" class="form-control" name="remote_address" id="remote_address" value="{{ old('remote_address') }}">
            </div>
            
            <div class="form-group">
                <label for="local_address">Local Address</label>
                <input type="text" class="form-control" name="local_address" id="local_address" value="{{ old('local_address') }}">
            </div>
            
            <div class="form-group">
                <label for="only_one">Only One</label>
                <select class="form-control" name="only_one" id="only_one">
                    <option value="">(default)</option>
                    <option value="yes" @selected(old('only_one')==='yes')>yes</option>
                    <option value="no" @selected(old('only_one')==='no')>no</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comment">Comment</label>
                <input type="text" class="form-control" name="comment" id="comment" value="{{ old('comment') }}">
            </div>
        </div>
        
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('mikrotik-profile.index') }}" class="btn btn-secondary ml-2">Cancel</a>
        </div>
    </form>
</div>
@stop