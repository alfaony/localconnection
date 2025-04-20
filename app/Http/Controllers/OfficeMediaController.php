<?php


namespace App\Http\Controllers;

use App\Models\OfficeMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OfficeMediaController extends Controller
{
    public function index()
    {
        $imageMedias = OfficeMedia::byCompany(Auth::user()->company_id)
            ->where('type','image')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        
        $youtubeMedias = OfficeMedia::byCompany(Auth::user()->company_id)
            ->where('type','youtube')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => 
            [
                'image' => view('partials.office-media-image', compact('imageMedias'))->render(),
                'youtube' => view('partials.office-media-youtube', compact('youtubeMedias'))->render()
            ]
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:image,youtube',
            'title' => 'nullable|string|max:255',
            'file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'youtube_url' => 'nullable|url',
            'is_temporary' => 'boolean'
        ]);

        try {
            $data = [
                'type' => $request->type,
                'title' => $request->title,
                'is_temporary' => $request->boolean('is_temporary', true),
            ];
    
            if ($request->type === 'image' && $request->hasFile('file')) {
                $path = $request->file('file')->store('media/images', 'public');
                $data['file_path'] = $path;
            }
    
            if ($request->type === 'youtube' && $request->youtube_url) {
                if (!$this->isValidYoutubeUrl($request->youtube_url)) {
                    return response()->json(['message' => 'URL YouTube tidak valid atau video tidak ditemukan.'], 422);
                }
                $data['youtube_url'] = $request->youtube_url;
            }
    
            $data['user_id'] = Auth::id();
            $data['company_id'] = Auth::user()->company_id;
            $media = OfficeMedia::create($data);
    
            return response()->json([
                'status' => 'success',
                'data' => $media,
                'request' => $request->all()
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed'
            ]);
        }
    }

    private function isValidYoutubeUrl($url)
    {
        $videoId = null;

        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        }

        if (!$videoId) return false;

        $response = Http::get("https://www.youtube.com/oembed", [
            'url' => "https://www.youtube.com/watch?v=$videoId",
            'format' => 'json'
        ]);

        return $response->ok();
    }

    public function destroy($id)
    {
        $media = OfficeMedia::byCompany(Auth::user()->company_id)->findOrFail($id);

        // Hapus file jika media adalah gambar
        if ($media->type === 'image' && Storage::exists($media->file_path)) {
            Storage::delete($media->file_path);
        }

        $media->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Media deleted successfully.'
        ]);
    }
}
