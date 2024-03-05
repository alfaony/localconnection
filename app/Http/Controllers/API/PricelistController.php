<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;

use App\Models\Product;

class PricelistController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paginate = 10;
        if ($request->per_page) {
            $paginate = $request->per_page;
        }
        $order = 'asc';
        if ($request->order == 'desc') 
        {
            $order = 'desc';
        }

        $product = Product::byCompany(auth()->user()->company_id)->where('name','like', '%' . $request->get('product') . '%')
        ->OrderBy('name',$order)->simplePaginate($paginate)->toArray();

        $totalProduct['total'] = Product::byCompany(auth()->user()->company_id)->count();

        return $this->sendResponse(array_merge($product,$totalProduct),'Success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $product = Product::where('slug',$slug)->firstOrFail();
        $activities = $product->activities()->orderBy('created_at', 'desc')->limit(5)->get();
        
        return $this->sendResponse($activities,'Success');
    }
}
