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
