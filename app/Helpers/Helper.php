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