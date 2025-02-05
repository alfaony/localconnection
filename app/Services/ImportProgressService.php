<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ImportProgressService
{
    public static function initialize(string $batchId)
    {
        Cache::put("import_progress:{$batchId}", [
            'total' => 0,
            'processed' => 0,
            'status' => 'processing',
        ]);
    }

    public static function increment(string $batchId)
    {
        $progress = Cache::get("import_progress:{$batchId}");
        $progress['processed']++;
        Cache::put("import_progress:{$batchId}", $progress);
    }

    public static function get(string $batchId)
    {
        return Cache::get("import_progress:{$batchId}");
    }
}