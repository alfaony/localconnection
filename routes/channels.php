<?php

use Illuminate\Support\Facades\Broadcast;
use App\Helpers\Helper;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('chat.item-request.{id}', function ($user, $id) {
//     return true; // atau cek $user->company_id == itemRequest->company_id
// });
Broadcast::channel('chat.item-request.{id}', function ($user, $id) {
    return true; // atau validasi custom, misal $user->company_id == ItemRequest::find($id)->company_id
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// QR untuk tampilan laptop
Broadcast::channel('office.barcode.{companyId}', function ($user, $companyId) {
    // return (int) $user->company_id === (int) $companyId;
    return true;
});

// Notifikasi ke user yang melakukan scan
Broadcast::channel('office.scan.{userId}', function ($user, $userId) {
    // return (int) $user->id === (int) $userId;
    return true;
});

// Subscription Chat – hanya user yang authenticated bisa join
// (validasi lebih ketat: cek apakah user adalah pemilik atau admin)
Broadcast::channel('subscription.chat.{subscriptionId}', function ($user, $subscriptionId) {
    // if (!$user) return false;

    // // Admin (has role/permission) atau customer pemilik subscription
    // $subscription = \App\Models\CustomerSubscription::find($subscriptionId);
    // if (!$subscription) return false;

    // // Customer pemilik
    // if ((int) $subscription->user_id === (int) $user->id) return true;

    // // User lain yang authenticated (admin/PIC)
    // // Bisa ditambah validasi role jika diperlukan
    // return $user->can('admin') || $user->hasAnyRole(['admin', 'super-admin', 'staff']);

    // return Access::can('manul')
    return true;
});

// AskBos – hasil AI dikirim langsung ke user via broadcast
Broadcast::channel('ask-bos.{userId}', function ($user, $userId) {
    return $user->id === $userId;
});

// AskBos (channel baru) – public channel, tidak butuh auth
Broadcast::channel('bos.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Employee Check-in popup – hanya user ybs yang boleh listen
Broadcast::channel('employee-checkin.{userId}', function ($user, $userId) {
    return $user->id === $userId;
});
