<?php

namespace App\Helpers;

use App\Models\Router;

class MikrotikConfig {
    public static function forServer(String $mikrotikId): array {
        $kv = Router::find($mikrotikId);
        
        return 
        [
            'host'     => $kv->host,
            'user'     => $kv->username,
            'pass'     => $kv->password,
            'port'     => (int)($kv->port ?? 8728),
            'ssl'      => filter_var($kv->ssl ?? false, FILTER_VALIDATE_BOOLEAN),
            'timeout'  => 10,
            'attempts' => 1,
            'legacy'   => false,
        ];
    }
}