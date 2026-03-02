<?php

namespace App\Http\Controllers;

use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Helpers\InboxHelper;
use App\Helpers\Access;

class InboxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = Auth::id();

        // Mass update dengan scope
        $unreadCount = Inbox::forUser($userId)
            ->notifications()
            ->update(['is_notif' => false]);

        // Query dengan scope yang sudah didefinisikan
        $inboxMessages = Inbox::forUser($userId)
            ->withSender()
            ->latest()
            ->paginate(10);

        $isShow = Access::can('show', 'inboxes');

        Cache::forget("inbox_unread_count_{$userId}");

        return view('inbox.index', compact('inboxMessages', 'unreadCount', 'isShow'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Mengambil pesan berdasarkan id
        $message = Inbox::findOrFail($id);
        
        $inboxNotif = new InboxHelper();
        $inboxNotif->read(Auth::id(), $id);

        $message->update([
            'is_read' => 1 // Menandai pesan telah dibaca
        ]);
        
        // Mengecek apakah pesan memiliki direct_url
        // Redirect ke URL yang ditentukan
        try {
            if ($message->direct_url) 
            {
                return redirect()->to($message->direct_url);
            } else {
                // Jika tidak ada direct_url, tampilkan pesan pada show.blade.php
                return redirect()->to(route('inbox.index'));
            }
        } catch (\Throwable $th) {
            return redirect()->to(route('inbox.index'));
        }
    }


    /**
     * Get the count of unread inbox messages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadcount()
    {
        $unreadCount = Inbox::where('user_id_to', Auth::id())->where('is_notif', true)->count();
        return response()->json(['unreadCount' => $unreadCount]);
    }

}
