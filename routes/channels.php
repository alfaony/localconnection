<?php

use Illuminate\Support\Facades\Broadcast;

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
