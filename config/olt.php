<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OLT polling
    |--------------------------------------------------------------------------
    |
    | Values are intentionally conservative so a slow OLT cannot keep a queue
    | worker busy for too long. Individual OLTs can configure their polling
    | interval from the UI.
    |
    */
    'timeout_seconds' => (int) env('OLT_SNMP_TIMEOUT', 3),
    'retries' => (int) env('OLT_SNMP_RETRIES', 1),
    'history_interval_minutes' => (int) env('OLT_HISTORY_INTERVAL', 15),
    'queue' => env('OLT_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Net-SNMP fallback
    |--------------------------------------------------------------------------
    |
    | The native PHP SNMP extension is preferred. When it is unavailable the
    | application uses these binaries without invoking a shell.
    |
    */
    'snmpget_binary' => env('OLT_SNMPGET_BINARY', '/usr/bin/snmpget'),
    'snmpwalk_binary' => env('OLT_SNMPWALK_BINARY', '/usr/bin/snmpwalk'),
];
