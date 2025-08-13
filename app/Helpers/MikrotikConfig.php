<?php

namespace App\Helpers;

use App\Models\SettingCompany;

class MikrotikConfig {
    public static function forCompany(String $companyId): array {
        $kv = SettingCompany::byCompany($companyId)
            ->where('menu','mikrotik')
            ->pluck('field_value', 'field_title');
        
        return 
        [
            'host'     => $kv['mikrotik_host'] ?? '',
            'user'     => $kv['mikrotik_username'] ?? '',
            'pass'     => $kv['mikrotik_password'] ?? '',
            'port'     => (int)($kv['mikrotik_port'] ?? 8728),
            'ssl'      => filter_var($kv['mikrotik_ssl'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'timeout'  => 10,
            'attempts' => 1,
            'legacy'   => false,
        ];
    }
}