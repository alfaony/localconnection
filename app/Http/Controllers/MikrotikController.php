<?php

namespace App\Http\Controllers;

use App\Models\InternetPackage;
use Illuminate\Http\Request;
use App\Services\MikrotikService;

class MikrotikController extends Controller
{
    public function provision(Request $request) {
        $data = $request->validate([
            'company_id' => 'required|String',
            'username'   => 'required|string',
            'password'   => 'required|string',
            'package_id' => 'required|integer',
            'comment'    => 'nullable|string'
        ]);

        // $pkg = InternetPackage::findOrFail($data['package_id']);
        // abort_if(!$pkg->ppp_profile_name, 422, 'Package belum punya PPP profile.');

        $mt = new MikrotikService($data['company_id']);
        $sec = $mt->secretFind($data['username']);

        $payload = [
            'password' => $data['password'],
            'profile'  => "HikariLite",
            'comment'  => $data['comment'] ?? '',
            'disabled' => 'no'
        ];

        if ($sec) {
            $mt->secretSet($sec['.id'], $payload);
        } else {
            $mt->secretAdd(['name' => $data['username']] + $payload);
        }
        $mt->activeDisconnectByName($data['username']);

        return response()->json(['status' => 'ok']);
    }

    public function cut(Request $request) {
        $data = $request->validate([
            'company_id' => 'required|String',
            'username'   => 'required|string',
        ]);

        $mt = new MikrotikService($data['company_id']);
        $sec = $mt->secretFind($data['username']);
        if ($sec) {
            $mt->secretSet($sec['.id'], ['disabled' => 'yes']);
            $mt->activeDisconnectByName($data['username']);
        }
        return response()->json(['status' => 'cut']);
    }

    public function restore(Request $request) {
        $data = $request->validate([
            'company_id' => 'required|String',
            'username'   => 'required|string',
            'package_id' => 'required|integer',
        ]);

        $pkg = InternetPackage::findOrFail($data['package_id']);
        $mt = new MikrotikService($data['company_id']);
        $sec = $mt->secretFind($data['username']);
        if ($sec) {
            $mt->secretSet($sec['.id'], [
                'profile'  => "HikariLite",
                'disabled' => 'no'
            ]);
            $mt->activeDisconnectByName($data['username']);
        }
        return response()->json(['status' => 'restored']);
    }
}