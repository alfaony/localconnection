<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\SortUrl;

class SortUrlController extends Controller
{
    public function index($slug)
    {
        $url = SortUrl::where('slug',$slug)->first();

        if (!$url) 
        {
            abort(403);
        }
        
        try {
            // ✅ Ambil file dari S3 dan stream ke user
            $filePath = $url->link_target;
            
            // Cek apakah file ada di S3
            if (!Storage::disk('s3')->exists($filePath)) {
                abort(404, 'File tidak ditemukan di S3');
            }
            
            // Dapatkan nama file asli
            $fileName = basename($filePath);
            
            // Stream file langsung dari S3
            return response()->streamDownload(function() use ($filePath) {
                echo Storage::disk('s3')->get($filePath);
            }, $fileName);
            
        } catch (\Throwable $th) {
            // throw $th;
            Loh::error($th);
            return redirect()->route('home');
        }
        // $filePath = storage_path('app/public'.$url->link_target);
    }
}
