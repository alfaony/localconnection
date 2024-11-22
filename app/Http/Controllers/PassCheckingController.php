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
        $pictures = [];
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $file) {
                $filePath = $file->store('public/pictures');
                $pictures[] = Storage::url($filePath);
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
        $passChecking = PassChecking::findOrFail($id);

        // Get the current pictures (ensure it's an array)
        $pictures = $passChecking->pictures ?? [];

        // Add new uploaded pictures to the array
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $file) {
                $filePath = $file->store('public/pictures'); // Store the file
                $pictures[] = Storage::url($filePath); // Add the URL to the pictures array
            }
        }
        // dd($request->all());
        // Remove specified pictures
        if ($request->has('delete_pictures')) {
            foreach ($request->delete_pictures as $removeIndex) {
                if (isset($pictures[$removeIndex])) {
                    // Convert URL to storage path
                    $relativePath = str_replace(asset('storage'), 'public', $pictures[$removeIndex]);
    
                    // Delete the file from storage if it exists
                    if (Storage::exists($relativePath)) {
                        Storage::delete($relativePath);
                    }
    
                    // Remove from the pictures array
                    unset($pictures[$removeIndex]);
                }
            }
        }
        // dd("here");

        // Update the Pass Checking record
        $passChecking->update([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
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
