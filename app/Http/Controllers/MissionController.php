<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Mission;
use App\Models\Vision;


class MissionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'mission' => 'required|string|max:255',
            'vision' => 'required|exists:visions,id'
        ]);


        Mission::create([
            'mission' => $request->mission,
            'vision_id' => $request->vision,
            'company_id' => Auth::user()->company_id,
        ]);

        return redirect()->route('vision.index')->with('success', 'Misi berhasil ditambahkan.');
    }

    public function update(Request $request, Mission $mission)
    {
        $request->validate([
            'mission' => 'required|string|max:255',
        ]);

        $mission->update([
            'mission' => $request->mission,
        ]);

        return redirect()->route('vision.index')->with('success', 'Misi berhasil diperbarui.');
    }

    public function destroy(Mission $mission)
    {
        $mission->delete();

        return redirect()->route('vision.index')->with('success', 'Misi berhasil dihapus.');
    }
}
