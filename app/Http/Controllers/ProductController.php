<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\ProductRequest;

use App\Models\Product;
use App\Models\ProductCategory;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $order = 'desc'; if($request->order == 'asc') { $order = 'asc'; }

        // Filter berdasarkan status penggunaan
        $filter = $request->get('filter', 'all'); // Default 'all'
        
        // Query produk utama
        $query = Product::byCompany(Auth::user()->company_id)
            ->where('name', 'like', '%' . $request->get('product') . '%');

        if ($filter === 'used') 
        {
            $query->where(function ($q) {
                $q->whereHas('quoteProducts')
                    ->orWhereHas('workOrderProducts')
                    ->orWhereHas('purchases')
                    ->orWhereHas('invoiceProducts');
            });
        } elseif ($filter === 'unused') {
            $query->whereDoesntHave('quoteProducts')
                ->whereDoesntHave('workOrderProducts')
                ->whereDoesntHave('purchases')
                ->whereDoesntHave('invoiceProducts');
        }

        $product = $query->orderBy('created_at', $order)->paginate(10);

        $productCategory = ProductCategory::byCompany(Auth::user()->company_id)
            ->orderBy('name', 'asc')
            ->get();

        $totalProduct = Product::byCompany(Auth::user()->company_id)->count();

        return view('product.index',compact('product','totalProduct', 'productCategory'));
    }

    // /**
    //  * Show the form for creating a new resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function create()
    // {
    //     dd("hore");
    // }

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
        $product->price_buy = $request->post('price_buy') ?? 0;
        $product->price_sell = $request->post('price_sell');
        $product->method_count = $request->post('method_count');
        $product->product_category_id = $request->product_category_id;
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

        $totalProduct = Product::byCompany(Auth::user()->company_id)->count();
        $productEdit = Product::where('slug', $slug)->firstOrFail();
        $product = Product::byCompany(Auth::user()->company_id)->OrderBy('name','asc')->paginate(10);
        $productCategory = ProductCategory::byCompany(Auth::user()->company_id)->OrderBy('name','asc')->get();
        
        return view('product.index', compact('productEdit','product','totalProduct','nomor','productCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $slug)
    {
        $product = Product::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $product->name = $request->post('name');
        $product->price_buy = $request->post('price_buy') ?? 0;
        $product->price_sell = $request->post('price_sell');
        $product->method_count = $request->post('method_count');
        $product->product_category_id = $request->product_category_id;
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
    public function destroy($slug)
    {
        $product = Product::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        // Cek apakah produk masih digunakan di tabel lain
        $isUsed = $product->quoteProducts()->exists() ||
        $product->workOrderProducts()->exists() ||
        $product->purchases()->exists() ||
        $product->invoiceProducts()->exists();

        if ($isUsed) 
        {
            return redirect()->back()->with('error', 'Produk tidak dapat dihapus karena masih digunakan.');
        }
        
        $product->delete();
        return redirect()->back()->with('delete',true);
    }
}
