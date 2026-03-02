{{-- edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit PPP Profile')

@section('content_header')
    <h1>Edit PPP Profile: {{ $profile['name'] }}</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Profile Settings</h3>
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

    <form method="post" action="{{ route('mikrotik-profile.update', $profile['name']) }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label for="new_name">New Name (opsional ganti nama)</label>
                <input type="text" class="form-control" name="new_name" id="new_name" value="{{ old('new_name') }}">
            </div>
            
            <div class="form-group">
                <label for="rate_limit">Rate Limit</label>
                <input type="text" class="form-control" name="rate_limit" id="rate_limit" 
                       value="{{ old('rate_limit', $profile['rate-limit'] ?? '') }}">
            </div>
            
            <div class="form-group">
                <label for="remote_address">Remote Address</label>
                <input type="text" class="form-control" name="remote_address" id="remote_address" 
                       value="{{ old('remote_address', $profile['remote-address'] ?? '') }}">
            </div>
            
            <div class="form-group">
                <label for="local_address">Local Address</label>
                <input type="text" class="form-control" name="local_address" id="local_address" 
                       value="{{ old('local_address', $profile['local-address'] ?? '') }}">
            </div>
            
            <div class="form-group">
                <label for="only_one">Only One</label>
                <select class="form-control" name="only_one" id="only_one">
                    <option value="">(tidak diubah)</option>
                    <option value="yes" @selected(($profile['only-one'] ?? '')==='yes')>yes</option>
                    <option value="no" @selected(($profile['only-one'] ?? '')==='no')>no</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comment">Comment</label>
                <input type="text" class="form-control" name="comment" id="comment" 
                       value="{{ old('comment', $profile['comment'] ?? '') }}">
            </div>
        </div>
        
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('mikrotik-profile.index') }}" class="btn btn-secondary ml-2">Cancel</a>
        </div>
    </form>
</div>
@stop