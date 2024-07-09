<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::byCompany(Auth::user()->company_id)->paginate(10);
        return view('product_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        ProductCategory::create(['name' => $request->name,'user_id' => Auth::id()]);

        return redirect()->route('product-category.index')->with('store', 'Category successfully created!');
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $category = ProductCategory::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $category->update(['name' => $request->name]);

        return redirect()->route('product-category.index')->with('update', 'Category successfully updated!');
    }

    public function destroy($slug)
    {
        $category = ProductCategory::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $category->delete();
        return redirect()->route('product-category.index')->with('delete', 'Category successfully deleted!');
    }
}
