<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionChat;
use App\Jobs\SentInbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionChatController extends Controller
{
    /**
     * GET /customer-software/subscription/{subscription}/chat
     * Return messages as JSON (always readable).
     */
    public function index(CustomerSubscription $subscription)
    {
        // Pastikan hanya pemilik subscription yang bisa akses
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $subscription->chats()
            ->with('sender:id,name')
            ->latest()
            ->take(100)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'can_chat' => $subscription->canChat(),
            'messages' => $messages->map(fn($m) => [
                'id'             => $m->id,
                'message'        => $m->message,
                'attachment_url' => $m->attachment_url,
                'sender_name'    => $m->sender->name ?? 'Unknown',
                'sender_id'      => $m->user_id,
                'is_me'          => $m->user_id === Auth::id(),
                'created_at'     => $m->created_at->format('d M Y H:i'),
            ]),
        ]);
    }

    /**
     * POST /customer-software/subscription/{subscription}/chat
     * Send a message (only if canChat).
     */
    public function store(Request $request, CustomerSubscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$subscription->canChat()) {
            return response()->json(['error' => 'Chat tidak tersedia. Subscription tidak aktif atau sudah expired.'], 403);
        }

        $request->validate([
            'message'    => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Pesan atau file wajib diisi.'], 422);
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('subscription_chats');
        }

        try {
            $chat = SubscriptionChat::create([
                'subscription_id' => $subscription->id,
                'user_id'         => Auth::id(),
                'message'         => $request->message,
                'attachment'      => $path,
            ]);

            // Kirim inbox ke PIC software
            $subscription->load('software.pic');
            $pic = $subscription->software->pic ?? null;

            if ($pic && $pic->id !== Auth::id()) {
                $url     = route('subscription.show', $subscription->id);
                $message = "💬 Customer *{$subscription->user->name}* mengirim pesan baru di subscription *{$subscription->software->nama}*: \"{$request->message}\"";
                SentInbox::dispatch(Auth::id(), $pic->id, $message, $url);
            }

            return response()->json([
                'success' => true,
                'message' => [
                    'id'             => $chat->id,
                    'message'        => $chat->message,
                    'attachment_url' => $chat->attachment_url,
                    'sender_name'    => Auth::user()->name,
                    'sender_id'      => Auth::id(),
                    'is_me'          => true,
                    'created_at'     => $chat->created_at->format('d M Y H:i'),
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error('SubscriptionChat store error: ' . $th->getMessage());
            return response()->json(['error' => 'Gagal mengirim pesan.'], 500);
        }
    }
}
