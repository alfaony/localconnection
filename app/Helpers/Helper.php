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

if (!function_exists('html_to_wa')) {
    /**
     * Konversi HTML dari Quill editor ke plain text untuk WhatsApp.
     * Mengubah <p>, <br> menjadi newline dan strip semua tag HTML.
     */
    function html_to_wa(string $html): string
    {
        // <p> closing → newline
        $text = preg_replace('/<\/p>/i', "\n", $html);
        // <br> → newline
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        // Hapus opening <p> dan tag lainnya
        $text = strip_tags($text);
        // Decode HTML entities (&amp; &nbsp; dll)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Bersihkan lebih dari 2 newline berturut-turut
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
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