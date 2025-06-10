<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class BroadcastAuthController extends Controller
{
      public function broadcastingAuthorize(Request $request)
    {

        return Broadcast::auth($request);
    }
}
