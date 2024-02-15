<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;

use App\Http\Requests\ProductRequest;

use App\Models\Product;

class ProductController extends BaseController
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price_buy' => 'nullable|numeric',
            'price_sell' => 'required|numeric',
            'method_count' => 'required|string|max:255'
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price_buy.required' => 'Harga beli wajib diisi.',
            'price_buy.numeric' => 'Harga beli harus berupa angka.',
            'price_sell.required' => 'Harga jual wajib diisi.',
            'price_sell.numeric' => 'Harga jual harus berupa angka.',
            'method_count.required' => 'Metode perhitungan wajib diisi.'
        ]);
    
        if($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $product = new Product();
        $product->name = $request->post('name');
        $product->price_buy = $request->post('price_buy') ?? 0;
        $product->price_sell = $request->post('price_sell');
        $product->method_count = $request->post('method_count');
        $product->user_created_id = Auth::user()->id;
        $product->user_updated_id = Auth::user()->id;
        $product->save();

        return $this->sendResponse($product,'Success');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $product = Product::byCompany(auth()->user()->company_id)->where('slug', $slug)->first();
        if(empty($product))
        {
            return $this->sendError('Product Not Found');
        }
        return $this->sendResponse($product,'Success');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::byCompany(auth()->user()->company_id)->where('id', $id)->first();
        if(empty($product))
        {
            return $this->sendError('Product Not Found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price_buy' => 'nullable|numeric',
            'price_sell' => 'required|numeric',
            'method_count' => 'required|string|max:255'
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price_buy.required' => 'Harga beli wajib diisi.',
            'price_buy.numeric' => 'Harga beli harus berupa angka.',
            'price_sell.required' => 'Harga jual wajib diisi.',
            'price_sell.numeric' => 'Harga jual harus berupa angka.',
            'method_count.required' => 'Metode perhitungan wajib diisi.'
        ]);
        
        if($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $product->name = $request->post('name');
        $product->price_buy = $request->post('price_buy') ?? 0;
        $product->price_sell = $request->post('price_sell');
        $product->method_count = $request->post('method_count');
        $product->user_updated_id = Auth::user()->id;
        $product->save();

        return $this->sendResponse($product,'Success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $product = Product::byCompany(auth()->user()->company_id)->where('slug', $slug)->first();
        if(empty($product))
        {
            return $this->sendError('Product Not Found');
        }
        $product->delete();

        return $this->sendMessage('Success');
    }
}
