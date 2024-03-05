<?php

namespace App\Http\Controllers\API;

use Validator;

use Illuminate\Http\Request;
use App\Rules\UniqueCustomer;

use App\Http\Controllers\API\BaseController as BaseController;

use App\Models\Customer;

class CustomerController extends BaseController
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

        $customer = Customer::byCompany(auth()->user()->company_id)->where('name','like', '%' . $request->get('customer') . '%')
        ->OrderBy('name',$order)->paginate($paginate)->toArray();

        $totalCustomer['total'] = Customer::byCompany(auth()->user()->company_id)->count();

        return $this->sendResponse(array_merge($customer,$totalCustomer),'Success');

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
        $validator = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'director' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'assignor' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => ['required', 'regex:/^\d{5,13}$/'],
            'email' => ['required','email',new UniqueCustomer('store')],
            'city' => 'nullable|string',
            'industry' => 'nullable|string',
        ],
        [
            'name.required' => 'Nama wajib diisi.',
            'director.required' => 'Direktur wajib diisi.',
            'pic.required' => 'PIC wajib diisi.',
            'assignor.required' => 'Penugasan wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        if($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $customer = new Customer();
        $customer->name = $request->post('name');
        $customer->director = $request->post('director');
        $customer->pic = $request->post('pic');
        $customer->assignor = $request->post('assignor');
        $customer->address = $request->post('address');
        $customer->phone = $request->post('phone');
        $customer->email = $request->post('email');
        $customer->city = $request->post('city');
        $customer->industry = $request->post('industry');
        $customer->user_created_id = auth()->user()->id;
        $customer->user_updated_id = auth()->user()->id;

        $customer->save();

        return $this->sendResponse($customer,"Success");
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
    public function edit($slug)
    {
        $customer = Customer::where('slug', $slug)->first();
        if(empty($customer))
        {
            return $this->sendError('Customer Not Found');
        }   
        return $this->sendResponse($customer,'Success');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::byCompany(auth()->user()->company_id)->where('id', $id)->first();
        if(empty($customer))
        {
            return $this->sendError('Customer Not Found');
        }
        $validator = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'director' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'assignor' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'industry' => 'nullable|string',
            'phone' => ['required', 'regex:/^\d{5,13}$/'],
            'email' => ['required','email',new UniqueCustomer('update',$customer->email)],
        ],
        [
            'name.required' => 'Nama wajib diisi.',
            'director.required' => 'Direktur wajib diisi.',
            'pic.required' => 'PIC wajib diisi.',
            'assignor.required' => 'Penugasan wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        if($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $customer->name = $request->post('name');
        $customer->director = $request->post('director');
        $customer->pic = $request->post('pic');
        $customer->assignor = $request->post('assignor');
        $customer->address = $request->post('address');
        $customer->phone = $request->post('phone');
        $customer->email = $request->post('email');
        $customer->city = $request->post('city');
        $customer->industry = $request->post('industry');
        $customer->user_updated_id = auth()->user()->id;

        $customer->save();

        return $this->sendResponse($customer,'Success');
    }   

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $customer = Customer::byCompany(auth()->user()->company_id)->where('slug', $slug)->first();
        if(empty($customer))
        {
            return $this->sendError('Customer Not Found');
        }
        $customer->delete();

        return $this->sendMessage('Success');

    }
}
