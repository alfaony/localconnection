<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PricelistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pricelist.index');
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
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = Product::query();

        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['name', 'price_sell'];

        // Define searchable columns
        $searchable = 
        [
            0 => 'name',
            1 => 'price_sell',
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [
            [
                'name' => 'Show',
                'route' => 'pricelist.show',
                'id' => true,
            ],
        ];

        $response =  datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        foreach ($data->data as $index => $item) 
        {
            $item->price_sell = 'Rp. '.number_format($item->price_sell, 0,',','.'); // Format angka dengan 2 desimal
        }

        return response()->json($data);
    }
}
