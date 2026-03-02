<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductStoreController extends Controller
{
    /**
     * Search product stores.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = ProductStore::with(['category', 'brand', 'rack', 'primaryMedia']);

            // Apply byCompany filter
            if (Auth::check()) {
                $query->byCompany(Auth::user()->company_id);
            }

            // Apply search filter (required)
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'name');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Limit results
            $limit = $request->input('limit', 20);
            $products = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $products,
                'count' => $products->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
