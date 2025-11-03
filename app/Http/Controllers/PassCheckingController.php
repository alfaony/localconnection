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

    public function edit($id)
    {
        $editing = PassChecking::byCompany(Auth::user()->company_id)->findOrFail($id);
        $passCheckings = PassChecking::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->paginate(5);

        return view('pass_checkings.index', compact('editing', 'passCheckings'));
    }

    public function show($id)
    {
        $passChecking = PassChecking::findOrFail($id); // Fetch the record
        return view('pass_checkings.show', compact('passChecking')); // Pass it to the view
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'pictures.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'Nama agenda harus diisi.',
            'name.string' => 'Nama agenda harus berupa teks.',
            'name.max' => 'Nama agenda tidak boleh lebih dari 255 karakter.',
            'date.required' => 'Tanggal harus diisi.',
            'date.date' => 'Tanggal tidak valid.',
            'date.after_or_equal' => 'Tanggal harus hari ini atau setelahnya.',
            'start_time.required' => 'Waktu mulai harus diisi.',
            'start_time.date_format' => 'Format waktu mulai tidak valid. Gunakan format HH:mm.',
            'end_time.required' => 'Waktu selesai harus diisi.',
            'end_time.date_format' => 'Format waktu selesai tidak valid. Gunakan format HH:mm.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'pictures.*.image' => 'File yang diunggah harus berupa gambar.',
            'pictures.*.mimes' => 'Gambar harus memiliki format jpeg, png, atau jpg.',
            'pictures.*.max' => 'Ukuran gambar tidak boleh lebih dari 2 MB.',
        ]);
        
        // Cek overlap jadwal pada hari yang sama
        $existingSchedules = PassChecking::where('date', $request->date)->where('user_id', Auth::user()->id)
        ->where(function ($query) use ($request) {
            $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                ->orWhere(function ($query) use ($request) {
                    $query->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>=', $request->end_time);
                });
        })
        ->exists();

        if ($existingSchedules) {
            return redirect()->back()->withErrors(['start_time' => 'Waktu yang dipilih bertabrakan dengan jadwal lain pada hari yang sama.'])->withInput();
        }

        $pictures = [];
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $file) {
                $filePath = $file->store('public/pictures');
                $pictures[] = s3_asset(true,10,$filePath);
            }
        }

        PassChecking::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'pictures' => $pictures, // Tidak perlu json_encode jika menggunakan casting
        ]);

        return redirect()->route('pass-checking.index')->with('success', 'Pass Checking created successfully.');
    }

    public function update(Request $request, $id)
    {
        $existingSchedules = PassChecking::where('id', '!=', $id)->where('date', $request->date)->where('user_id', Auth::user()->id)
        ->where(function ($query) use ($request) {
            $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                ->orWhere(function ($query) use ($request) {
                    $query->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>=', $request->end_time);
                });
        })
        ->exists();

        if ($existingSchedules) {
            return redirect()->back()->withErrors(['start_time' => 'Waktu yang dipilih bertabrakan dengan jadwal lain pada hari yang sama.'])->withInput();
        }

        $passChecking = PassChecking::findOrFail($id);
        // Get the current pictures (ensure it's an array)
        $pictures = $passChecking->pictures ?? [];

        // Add new uploaded pictures to the array
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $file) {
                $filePath = $file->store('public/pictures'); // Store the file
                $pictures[] = s3_asset(true,10,$filePath); // Add the URL to the pictures array
            }
        }
        
        if ($request->has('key') && $request->hasFile('image')) 
        {
            $pictures = $passChecking->pictures;
            $key = $request->input('key');
    
            if (isset($pictures[$key])) {
                // Delete the old image
                Storage::delete(str_replace(s3_asset(true,10,''), '', $pictures[$key]));
    
                // Save the new image
                $newImage = $request->file('image')->store('public/pictures');
                $pictures[$key] = s3_asset(true,10,$newImage);
            }
    
            $passChecking->pictures = $pictures;
        }
        // Remove specified pictures
        if ($request->has('delete_pictures')) {
            $deletePictures = json_decode($request->input('delete_pictures'), true);
            if (is_array($deletePictures)) 
            {
                foreach ($deletePictures as $removeIndex) {
                    if (isset($pictures[$removeIndex])) {
                        // Convert URL to storage path
                        $relativePath = str_replace(asset('storage'), $pictures[$removeIndex]);
        
                        // Delete the file from storage if it exists
                        if (Storage::exists($relativePath)) {
                            Storage::delete($relativePath);
                        }
        
                        // Remove from the pictures array
                        unset($pictures[$removeIndex]);
                    }
                }
            }
        }
        // dd("here");

        // Update the Pass Checking record
        $passChecking->update([
            'user_id' => Auth::user()->id,
            'name' => $request->name ?? $passChecking->name,
            'date' => $request->date ?? $passChecking->date,
            'start_time' => $request->start_time ?? $passChecking->start_time, 
            'end_time' => $request->end_time ?? $passChecking->end_time,
            'pictures' => array_values($pictures), // Reset array indices
        ]);

        return redirect()->route('pass-checking.index')->with('success', 'Pass Checking updated successfully.');
    }

    public function destroy(PassChecking $passChecking)
    {
        $passChecking->delete();
        return redirect()->route('pass-checking.index')->with('success', 'Pass Checking deleted successfully.');
    }
}
