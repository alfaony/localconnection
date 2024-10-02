<?php

namespace App\Http\Controllers;

use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\InboxHelper;

class InboxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Mengambil semua pesan yang dikirim oleh pengguna yang sedang login
        $inboxMessages = Inbox::where('user_id_to', Auth::user()->id)
                            ->orderBy('created_at', 'desc') // Mengurutkan berdasarkan created_at secara menurun
                            ->paginate(10); // Melakukan paginasi dengan 10 item per halaman

        $unreadMessage = Inbox::select('id')->where('user_id_to', Auth::user()->id)
            ->where('is_read', 0)
            ->orderBy('created_at', 'desc') // Mengurutkan berdasarkan created_at secara menurun
            ->get();
        
        $inboxNotif = new InboxHelper();
        $inboxNotif->readAll(Auth::id(),$unreadMessage);

        return view('inbox.index', compact('inboxMessages','unreadMessage'));
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
        $unreadCount = Inbox::where('user_id_to', Auth::id())->where('is_read', false)->count();

        return response()->json(['unreadCount' => $unreadCount]);
    }

}
