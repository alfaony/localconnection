<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('s3_asset')) {
    /**
     * Generate a full URL for an S3 object.
     *
     * @param  string  $path
     * @param  bool  $temporary
     * @param  int  $minutes
     * @return string
     */
    function s3_asset(Bool $temporary = null,int $minutes = null, string $path = null)
    {
        if ($temporary && $path)
        {
            $minutes = 10;
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes($minutes));
        }

        if (!$temporary && !$minutes && $path)
        {
            return Storage::disk('s3')->url($path);
        }

        return Storage::disk('s3')->url($path);
    }
}

if (!function_exists('s3_exists')) {
    function s3_exists(string $path = null): bool
    {
        if (!$path) return false;
        return Storage::disk('s3')->exists($path);
    }
}

if (!function_exists('s3_to_base64')) {
    /**
     * Fetch a file from S3 and return it as a base64 data URI.
     * Digunakan khusus untuk domPDF agar gambar terbaca dari S3.
     */
    function s3_to_base64(string $path = null): string
    {
        if (!$path) return '';
        try {
            $content  = Storage::disk('s3')->get($path);
            $mimeType = Storage::disk('s3')->mimeType($path);
            return 'data:' . $mimeType . ';base64,' . base64_encode($content);
        } catch (\Exception $e) {
            return '';
        }
    }
}