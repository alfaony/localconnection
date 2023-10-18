<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\ProductRequest;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $product = Product::where('name','like', '%' . $request->get('product') . '%')
        ->OrderBy('created_at','asc')->paginate(10);

        $totalProduct = count(Product::get());

        return view('product.index',compact('product','totalProduct'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        dd("hore");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $product = new Product();
        $product->name = $request->post('name');
        $product->price_buy = $request->post('price_buy');
        $product->price_sell = $request->post('price_sell');
        $product->method_count = $request->post('method_count');
        $product->user_created_id = Auth::user()->id;
        $product->user_updated_id = Auth::user()->id;
        $product->save();

        return redirect()->back()->with('store',true);
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
    public function edit($slug,Request $request)
    {
        $nomor = $request->get('nomor') ?? 0;

        $totalProduct = count(Product::get());
        $productEdit = Product::where('slug', $slug)->firstOrFail();
        $product = Product::OrderBy('created_at','asc')->paginate(10);
        
        return view('product.index', compact('productEdit','product','totalProduct','nomor'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        $product->name = $request->post('name');
        $product->price_buy = $request->post('price_buy');
        $product->price_sell = $request->post('price_sell');
        $product->method_count = $request->post('method_count');
        $product->user_updated_id = Auth::user()->id;
        $product->save();

        return redirect()->to(route('product.index'))->with('update',true);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('delete',true);
    }
}
