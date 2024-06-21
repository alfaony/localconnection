<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\ShiftingOb;

class ShiftingObController extends Controller
{
    public function index()
    {
        $shifts = ShiftingOb::byCompany(Auth::user()->company_id)->with('user')->paginate(10);
        return view('shifting.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'clock_in' => 'required',
        ]);

        ShiftingOb::create([
            'name' => $request->name,
            'clock_in' => $request->clock_in,
            'user_id' => Auth::user()->id,
        ]);

        return redirect()->route('shifting.index')->with('success', 'Shifting berhasil dibuat.');
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'clock_in' => 'required',
        ]);

        $shifting = ShiftingOb::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $shifting->update([
            'name' => $request->name,
            'clock_in' => $request->clock_in,
            'user_id' => Auth::user()->id,
        ]);

        return redirect()->route('shifting.index')->with('success', 'Shifting berhasil diperbarui.');
    }

    public function destroy($slug)
    {
        $shifting = ShiftingOb::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $shifting->delete();

        return redirect()->route('shifting.index')->with('success', 'Shifting berhasil dihapus.');
    }
}
