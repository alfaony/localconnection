<?php

namespace App\Http\Controllers;

use App\Helpers\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;
use App\Models\ProductCategory;

use App\Schemas\ParamSchema;
class PricelistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productCategories = ProductCategory::byCompany(Auth::user()->company_id)->get();

        return view('pricelist.index', compact('productCategories'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $activities = $product->activities()->orderBy('created_at', 'desc')->paginate(10);
        return view('pricelist.show',compact('product','activities'));
    }

    /**
     * Datatable Load Product
     */
    public function dataTableJson(Request $request)
    {
        // Fetch data for the DataTable
        $query = Product::query()
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->select('products.*', 'product_categories.name as product_category_name');
    
        // Filter by company ID
        $query->byCompany(Auth::user()->company_id);
    
        // Filter by product category if provided
        if ($request->has('product_category_id') && !empty($request->product_category_id)) {
            if($request->product_category_id != ParamSchema::ALL)
            {
                $query->where('products.product_category_id', $request->product_category_id);
            }
        } else {
            // Default to products without a category
            $query->whereNull('products.product_category_id');
        }
    
        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['products.name', 'product_categories.name', 'products.price_sell'];
    
        // Define searchable columns
        $searchable = [
            0 => 'products.name',
            // 2 => 'products.price_sell',
        ];
    
        // Define your bootstrap version (4 or 5)
        $bootstrap = 4;
    
        // Add action buttons to each row
        $actionButtons = [];
    
        if (Access::can('show', 'pricelists')) {
            $show = [
                'name' => 'Show',
                'route' => 'pricelist.show',
                'id' => true,
            ];
    
            array_push($actionButtons, $show);
        }
    
        $response = datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);
    
        $data = $response->getData();
        foreach ($data->data as $index => $item) {
            $item->price_sell = 'Rp. ' . number_format($item->price_sell, 0, ',', '.'); // Format angka dengan 2 desimal
        }
    
        return response()->json($data);
    }
    
    


}
