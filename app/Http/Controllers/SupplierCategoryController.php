<?php

namespace App\Http\Controllers;

use App\Models\SupplierCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierCategoryController extends Controller
{
    public function index()
    {
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->paginate(10);
        return view('supplier_category.index', compact('categories'));
    }

    public function create()
    {
        return view('supplier_category.createOrEdit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|'
        ]);

        SupplierCategory::create([
            'name' => $request->name,
            'company_id' => Auth::user()->company_id,
        ]);

        return redirect()->route('supplier-category.index')->with('success', 'Category created successfully.');
    }

    public function edit(SupplierCategory $supplierCategory)
    {
        return redirect()->route('supplier-category.index', ['edit' => $supplierCategory->id]);
    }

    public function update(Request $request, SupplierCategory $supplierCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        
        $supplierCategory->update([
            'name' => $request->name,
        ]);

        return redirect()->route('supplier-category.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(SupplierCategory $supplierCategory)
    {
        $supplierCategory->delete();
        return redirect()->route('supplier-category.index')->with('success', 'Category deleted successfully.');
    }
}
