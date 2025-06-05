<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemRequest;
use App\Models\ChatMessage;
use App\Events\ChatMessageSent;
use Illuminate\Support\Facades\Auth;   
use App\Helpers\InboxHelper;
use App\Jobs\SentInbox;

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

        $data = 
        [
            'status' => $itemRequest->status == "DELIVERED" ? false : true,
            'message' => $messages
        ];
        
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_request_id' => 'required|exists:item_requests,id',
            'message' => 'required|string|max:1000',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        $path = null;
        if ($request->hasFile('file')) 
        {
            $path = $request->file('file')->store('chat_uploads', 'public');
        }
        
        $chat = ChatMessage::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'item_request_id' => $data['item_request_id'],
            'message' => $data['message'],
            'attachment' => $path
        ]);

        
        
        // broadcast(new ChatMessageSent(
        //     $chat->sender->name ?? 'Anonim',
        //     $chat->message,
        //     $chat->created_at,
        //     $chat->item_request_id,
        //     Auth::user()->id
        // ))->toOthers();
        

         $userIds = ChatMessage::where('item_request_id', $data['item_request_id'])
        ->where('user_id', '!=', auth()->id())
        ->distinct()
        ->pluck('user_id')->push($chat->user_id)->push($chat->itemRequest->user_id)->push($chat->itemRequest->assigned_pic_id)->unique();

        // Kirim inbox ke user-user tersebut
        $directUrl = route('item-request.show', $data['item_request_id']);
        $message = $request->message;

        foreach ($userIds as $userId) 
        {
            if($userId != Auth::user()->id)
            {
                SentInbox::dispatch(Auth::user()->id,$userId,$message, $directUrl);
            }
        }

            return response()->json(['success' => true]);
        }

    public function sentInbox($to,$message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            Auth::user()->id, 
            $message, 
            $directUrl
        );

        return;
    }
}
