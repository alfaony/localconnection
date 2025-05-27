<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemRequest;
use App\Models\ChatMessage;
use App\Events\ChatMessageSent;

class ChatMessageController extends Controller
{
    public function show($id)
    {
        $itemRequest = ItemRequest::byCompany(auth()->user()->company_id)->find($id);
        $messages = $itemRequest->chatMessages()->with('sender')
        ->latest()
        ->take(50)
        ->get()
        ->reverse()
        ->values(); // optional untuk reset index
        
        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_request_id' => 'required|exists:item_requests,id',
            'message' => 'required|string|max:1000',
        ]);

        $chat = ChatMessage::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'item_request_id' => $data['item_request_id'],
            'message' => $data['message'],
        ]);
        
        broadcast(new ChatMessageSent(
            $chat->sender->name ?? 'Anonim',
            $chat->message,
            $chat->created_at,
            $chat->item_request_id
        ))->toOthers();

        return response()->json(['success' => true]);
    }
}
