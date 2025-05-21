<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemRequest;
use App\Models\SupplierCategory;
use Illuminate\Support\Facades\Auth;

class ItemRequestController extends Controller
{
    public function index()
    {
        $requests = ItemRequest::byCompany(auth()->user()->company_id)->latest()->paginate(10);
        return view('item_request.index', compact('requests'));
    }

    public function create()
    {
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('item_request.createOrEdit', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_category_id' => 'required|exists:supplier_categories,id',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'required|numeric|min:1',
            'qty'=> "required|numeric|min:1"
        ]);


        $validated['user_id'] = auth()->id();
        $validated['company_id'] = auth()->user()->company_id;
        $validated['status'] = 'REQUESTED';

        ItemRequest::create($validated);

        return redirect()->route('item-request.index')->with('success', 'Request submitted.');
    }

    public function edit(ItemRequest $itemRequest)
    {
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('item_request.createOrEdit', compact('itemRequest', 'categories'));
    }

    public function update(Request $request, ItemRequest $itemRequest)
    {
        $validated = $request->validate([
            'supplier_category_id' => 'required|exists:supplier_categories,id',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'required|numeric|min:1',
            'qty' => 'required|numeric|min:1',
        ]);

        $itemRequest->update($validated);

        return redirect()->route('item-request.index')->with('success', 'Request updated.');
    }

    public function destroy(ItemRequest $itemRequest)
    {
        $itemRequest->delete();
        return redirect()->route('item-request.index')->with('success', 'Request deleted.');
    }
}
