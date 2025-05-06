<?php

namespace App\Http\Controllers;

use App\Models\SubscribeLetter;
use App\Models\User;
use Illuminate\Http\Request;

class SubscribeLetterController extends Controller
{
    public function index()
    {
        $letters = SubscribeLetter::byCompany(auth()->user()->company_id)->with('responsibleUser')->latest()->paginate(10);
        return view('subscribe_letter.index', compact('letters'));
    }

    public function create()
    {
        $users = User::byCompany(auth()->user()->company_id)->pluck('name', 'id');
        return view('subscribe_letter.createOrEdit', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'pic_user_id' => 'required|uuid|exists:users,id',
        ]);
    
        $filePath = null;
        if ($request->hasFile('document_path')) {
            $filePath = $request->file('document_path')->store('subscribe_letter', 'public');
        }

        SubscribeLetter::create([
            'name' => $request->name,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'pic_user_id' => $request->pic_user_id,
            'company_id' => auth()->user()->company_id,
            'document_path' => $filePath, // opsional,
        ]);

        return redirect()->route('subscribe-letter.index')->with('store', true);
    }

    public function show($id)
    {
        $letter = SubscribeLetter::byCompany(auth()->user()->company_id)->with(['activities', 'responsibleUser'])->findOrFail($id);
        return view('subscribe_letter.show', compact('letter'));
    }

    public function edit($id)
    {
        $letter = SubscribeLetter::byCompany(auth()->user()->company_id)->findOrFail($id);
        $users = User::pluck('name', 'id');
        return view('subscribe_letter.createOrEdit', compact('letter', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'pic_user_id' => 'nullable|uuid|exists:users,id',
        ]);

        $letter = SubscribeLetter::byCompany(auth()->user()->company_id)->findOrFail($id);

        $filePath = $letter->document_path ?? null;
        if ($request->hasFile('document_path')) 
        {
            $filePath = $request->file('document_path')->store('subscribe_letter', 'public');
        }

        $letter->update([
            'name' => $request->input('name', $letter->name),
            'valid_from' => $request->input('valid_from', $letter->valid_from),
            'pic_user_id' => $request->input('pic_user_id', $letter->pic_user_id),
            'valid_until' => $request->valid_until,
            'document_path' => $filePath,
        ]);

        return redirect()->route('subscribe-letter.show', $letter->id)->with('update', true);
    }

    public function destroy($id)
    {
        $letter = SubscribeLetter::byCompany(auth()->user()->company_id)->findOrFail($id);
        $letter->delete();

        return redirect()->route('subscribe-letter.index')->with('delete', true);
    }
}
