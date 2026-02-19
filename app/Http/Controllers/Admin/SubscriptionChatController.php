<?php

namespace App\Http\Controllers\Admin;

use App\Events\SubscriptionChatSent;
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
     * GET /subscription/{subscription}/chat
     * Return messages as JSON (always readable).
     */
    public function index(CustomerSubscription $subscription)
    {
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
     * POST /subscription/{subscription}/chat
     * Send a message as admin (only if canChat).
     */
    public function store(Request $request, CustomerSubscription $subscription)
    {
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

            // Broadcast ke channel private subscription (kirim ke semua subscriber termasuk customer)
            broadcast(new SubscriptionChatSent(
                $chat->id,
                Auth::user()->name,
                $chat->message ?? '',
                $chat->attachment_url,
                $chat->created_at->format('d M Y H:i'),
                (string) $subscription->id,
                Auth::id()
            ));

            // Kirim inbox ke semua user yang pernah terlibat dalam chat + customer
            $subscription->load('user', 'software');
            $customer = $subscription->user;

            $userIds = SubscriptionChat::where('subscription_id', $subscription->id)
                ->where('user_id', '!=', Auth::id())
                ->distinct()
                ->pluck('user_id');

            // Tambahkan customer jika ada dan bukan pengirim
            if ($customer) {
                $userIds->push($customer->id);
            }

            $userIds = $userIds->unique()->filter();

            $url     = route('customer-software.subscription.show', $subscription->id);
            $message = "💬 Ada pesan baru dari admin untuk subscription *{$subscription->software->nama}*: \"{$request->message}\"";

            // foreach ($userIds as $userId) {
            //     if ($userId != Auth::id()) {
            //         SentInbox::dispatch(Auth::id(), $userId, $message, $url);
            //     }
            // }

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
            dd($th);
            Log::error('Admin SubscriptionChat store error: ' . $th->getMessage());
            return response()->json(['error' => 'Gagal mengirim pesan.'], 500);
        }
    }
}
