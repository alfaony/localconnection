<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\CustomerRequest;

use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customer = Customer::where('name','like', '%' . $request->get('customer') . '%')
        ->OrderBy('created_at','asc')->paginate(10);

        $totalCustomer = count(Customer::get());

        return view('customer.index',compact('customer','totalCustomer'));
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
    public function store(CustomerRequest $request)
    {
        $customer = new Customer();
        $customer->name = $request->post('name');
        $customer->director = $request->post('director');
        $customer->pic = $request->post('pic');
        $customer->assignor = $request->post('assignor');
        $customer->address = $request->post('address');
        $customer->phone = $request->post('phone');
        $customer->email = $request->post('email');
        $customer->user_created_id = Auth::user()->id;
        $customer->user_updated_id = Auth::user()->id;

        $customer->save();

        return redirect()->back()->with('success',true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit($slug,Request $request)
    {
        $nomor = $request->get('nomor') ?? 0;

        $totalCustomer = count(Customer::get());
        $customerEdit = Customer::where('slug', $slug)->firstOrFail();
        $customer = Customer::OrderBy('created_at','asc')->paginate(10);
        
        return view('customer.index', compact('customerEdit','customer','totalCustomer','nomor'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        $customer->name = $request->post('name');
        $customer->director = $request->post('director');
        $customer->pic = $request->post('pic');
        $customer->assignor = $request->post('assignor');
        $customer->address = $request->post('address');
        $customer->phone = $request->post('phone');
        $customer->email = $request->post('email');
        $customer->user_updated_id = Auth::user()->id;

        $customer->save();

        return redirect()->to(route('customer.index'))->with('update',true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->back()->with('delete',true);
    }
}
