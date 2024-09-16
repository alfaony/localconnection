<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::where('company_id',Auth::user()->company_id)->paginate(10);
        return view('position.index', compact('positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|uuid|exists:companies,id',
        ]);

        Position::create($request->all());
        return redirect()->route('position.index')->with('success', 'Position created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $position = Position::where('company_id',Auth::user()->company_id)->find($id);
        $position->update($request->all());
        return redirect()->route('position.index')->with('success', 'Position updated successfully.');
    }

    public function destroy($id)
    {
        Position::where('company_id',Auth::user()->company_id)->find($id)->delete();
        return redirect()->route('position.index')->with('success', 'Position deleted successfully.');
    }
}
