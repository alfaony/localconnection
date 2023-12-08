<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $filePath = storage_path('app/public'.$url->link_target);
        return response()->download($filePath);
    }
}
