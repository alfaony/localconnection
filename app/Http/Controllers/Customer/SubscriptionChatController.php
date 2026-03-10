<?php

namespace App\Http\Controllers\Customer;

use App\Events\SubscriptionChatSent;
use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionChat;
use App\Jobs\SentInbox;
use Carbon\Carbon;
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

        // Anti-Spam Check: Limit customer to 3 consecutive messages
        $lastChats = SubscriptionChat::where('subscription_id', $subscription->id)
            ->latest()
            ->take(3)
            ->get();
            
        if ($lastChats->count() === 3) {
            $allFromMe = true;
            foreach ($lastChats as $c) {
                if ($c->user_id !== Auth::id()) {
                    $allFromMe = false;
                    break;
                }
            }
            if ($allFromMe) {
                return response()->json(['error' => 'Mohon tunggu balasan dari admin sebelum mengirim pesan lagi (Maksimal 3 pesan beruntun).'], 429);
            }
        }

        try {
            $chat = SubscriptionChat::create([
                'subscription_id' => $subscription->id,
                'user_id'         => Auth::id(),
                'message'         => $request->message,
                'attachment'      => $path,
            ]);

            // Broadcast ke channel private subscription (kirim ke semua subscriber termasuk admin)
            broadcast(new SubscriptionChatSent(
                $chat->id,
                Auth::user()->name,
                $chat->message ?? '',
                $chat->attachment_url,
                $chat->created_at->format('d M Y H:i'),
                (string) $subscription->id,
                Auth::id()
            ));

            // --- INBOX COOLDOWN LOGIC ---
            // 1. Ambil waktu chat TERAKHIR dari SI PENGIRIM (Customer) di ruang ini (sebelum chat yang baru ini)
            $lastChatFromSender = SubscriptionChat::where('subscription_id', $subscription->id)
                ->where('user_id', Auth::id())
                ->where('id', '!=', $chat->id) // Jangan hitung chat yang baru dibuat
                ->latest()
                ->first();

            // 2. Ambil waktu chat TERAKHIR dari LAWAN BICARA (Admin/PIC) di ruang ini
            $lastChatFromReceiver = SubscriptionChat::where('subscription_id', $subscription->id)
                ->where('user_id', '!=', Auth::id())
                ->latest()
                ->first();

            $shouldSendInbox = false;

            if (!$lastChatFromSender) {
                // Kasus: Ini benar-benar chat PERTAMA DIA di ruang ini
                $shouldSendInbox = true; 
            } else {
                $minutesSinceMyLastChat = now()->diffInMinutes($lastChatFromSender->created_at);
                
                if ($minutesSinceMyLastChat >= 5) {
                    $shouldSendInbox = true; // Kasus: Udah kelamaan nganggur > 5 mnt, send alert!
                } else {
                    $shouldSendInbox = false; // Hindari spam beruntun diri sendiri
                }
            }

            // 3. Pengecualian Kasus Ping-Pong Aktif
            if ($shouldSendInbox && $lastChatFromReceiver) {
                $minutesSinceHeReplied = now()->diffInMinutes($lastChatFromReceiver->created_at);
                // Kalau lawan bicara baru saja ngechat di bawah 5 menit yang lalu, Asumsinya kita sedang asyik tik-tok-an live chat
                if ($minutesSinceHeReplied < 5) {
                    $shouldSendInbox = false;
                }
            }

            if ($shouldSendInbox) {
                // Kirim inbox ke PIC / Admin
                $subscription->load('software.pic', 'user');
                
                $userIds = SubscriptionChat::where('subscription_id', $subscription->id)
                    ->where('user_id', '!=', Auth::id())
                    ->distinct()
                    ->pluck('user_id');

                $pic = $subscription->software->pic ?? null;
                if ($pic) {
                    $userIds->push($pic->id);
                }

                $userIds = $userIds->unique()->filter();
                $url     = route('subscription.show', $subscription->id);
                $message = "💬 Customer *{$subscription->user->name}* mengirim pesan baru di subscription *{$subscription->software->nama}*: \"{$request->message}\"";

                foreach ($userIds as $userId) {
                    if ($userId != Auth::id()) {
                        SentInbox::dispatch(Auth::id(), $userId, $message, $url);
                    }
                }
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
