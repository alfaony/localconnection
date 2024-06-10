<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Vision;

class VisionController extends Controller
{
    public function index()
    {
        $vision = Vision::where('company_id',Auth::user()->company_id)->with('missions')->first();
        $missions = $vision ? $vision->missions()->paginate(5) : NULL ; // Adjust the number 5 to the desired items per page

        return view('vision_mission.index', compact('vision','missions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vision' => 'required|string|max:255',
        ]);

        Vision::create([
            'vision' => $request->vision,
            'company_id' => Auth::user()->company_id,
        ]);

        return redirect()->route('vision.index')->with('success', 'Visi berhasil ditambahkan.');
    }

    public function update(Request $request, Vision $vision)
    {
        $request->validate([
            'vision' => 'required|string|max:255',
        ]);

        $vision->update([
            'vision' => $request->vision,
        ]);

        return redirect()->route('vision.index')->with('success', 'Visi berhasil diperbarui.');
    }
}
