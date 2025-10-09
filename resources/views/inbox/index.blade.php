@extends('adminlte::page')

@section('content_header')
    <h2>Inbox</h2>
@stop

@section('content')
<div class="container">
    @if ($inboxMessages->isEmpty())
        <div class="alert alert-info" role="alert">
            Tidak ada pesan yang ditemukan.
        </div>
    @else
        @if($unreadMessage->count() > 0)
            <div class="alert alert-warning" role="alert">
                Anda memiliki {{ $unreadMessage->count() }} pesan belum dibaca.
            </div>
        @endif
        <div class="list-group">
            @foreach ($inboxMessages as $message)
                @if($isShow)
                <a href="{{ route('inbox.show', $message->id) }}" 
                   class="list-group-item list-group-item-action {{ $message->is_read ? 'bg-light text-muted' : 'bg-white unread-message' }} mb-3 shadow-sm" 
                   style="border-radius: 10px; overflow: hidden;">
                   
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            @if(!$message->is_read)
                                <span class="badge badge-primary mr-2">New</span>
                            @endif
                            <h5 class="mb-1 {{ $message->is_read ? 'font-weight-normal' : 'font-weight-bold' }}">
                                {{ $message->message }}
                            </h5>
                        </div>
                        <small>{{ $message->created_at->diffForHumans() }}</small>
                    </div>

                    <!-- <p class="mb-1">{{ Str::limit($message->message, 100) }}</p> -->
                    <small>Dari: {{ $message->userFrom->name }}</small>

                    @if($message->is_read)
                        <i class="fas fa-check-circle text-success float-right mt-2"></i>
                    @else
                        <i class="fas fa-envelope text-primary float-right mt-2"></i>
                    @endif
                </a>
                @endif
            @endforeach
        </div>
        
        <div class="mt-4">
            {{ $inboxMessages->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    @endif
</div>
@endsection