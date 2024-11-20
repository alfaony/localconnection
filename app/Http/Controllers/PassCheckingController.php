<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\PassChecking;

class PassCheckingController extends Controller
{
    public function index()
    {
        $passCheckings = PassChecking::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->paginate(5);
        return view('pass_checkings.index', compact('passCheckings'));
    }

    public function store(Request $request)
    {
        $pictures = [];
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $file) {
                $filePath = $file->store('public/pictures');
                $pictures[] = Storage::url($filePath);
            }
        }

        PassChecking::create([
            'user_id' => Auth::user()->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'pictures' => $pictures, // Tidak perlu json_encode jika menggunakan casting
        ]);

        return redirect()->route('pass-checking.index')->with('success', 'Pass Checking created successfully.');
    }

    public function update(Request $request, $id)
    {
        $passChecking = PassChecking::findOrFail($id);

        $pictures = $passChecking->pictures ?? []; // Data akan otomatis menjadi array jika menggunakan casting
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $file) {
                $filePath = $file->store('public/pictures');
                $pictures[] = Storage::url($filePath);
            }
        }

        // Hapus gambar lama jika diminta
        if ($request->has('remove_pictures')) {
            foreach ($request->remove_pictures as $remove) {
                if (($key = array_search($remove, $pictures)) !== false) {
                    $relativePath = str_replace(asset('storage'), 'public', $remove);
                    if (Storage::exists($relativePath)) {
                        Storage::delete($relativePath);
                    }
                    unset($pictures[$key]);
                }
            }
        }

        $passChecking->update([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'pictures' => array_values($pictures), // Pastikan array terurut kembali
        ]);

        return redirect()->route('pass-checking.index')->with('success', 'Pass Checking created successfully.');
    }


    public function destroy(PassChecking $passChecking)
    {
        $passChecking->delete();
        return redirect()->route('pass-checking.index')->with('success', 'Pass Checking deleted successfully.');
    }
}
