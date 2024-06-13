<?php

namespace App\Http\Controllers;

use App\Models\DailyTaskCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DailyTaskCategoryController extends Controller
{
    public function index()
    {
        $categories = DailyTaskCategory::byCompany(Auth::user()->company_id)->get();
        return view('daily_task_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DailyTaskCategory::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
        ]);

        return redirect()->route('daily-task-category.index')->with('store', 'Category berhasil dibuat!');
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = DailyTaskCategory::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('daily-task-category.index')->with('update', 'Category berhasil diperbarui!');
    }

    public function destroy($slug)
    {
        $categories = DailyTaskCategory::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $categories->delete();
        return redirect()->route('daily-task-category.index')->with('delete', 'Category berhasil dihapus!');
    }
}

