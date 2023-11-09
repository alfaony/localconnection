<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Http\Requests\UserRequest;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = User::where('delete_able',1)
                ->where('email','like', '%' . $request->get('email') . '%')
                ->OrderBy('name','asc')->paginate(10);

        $totalUser = count(User::where('delete_able',1)->get());

        return view('user.index',compact('user','totalUser'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $user = new User();
        $user->name = $request->post('name'); 
        $user->email = $request->post('email');
        $user->phone = $request->post('phone');
        $user->password = bcrypt($request->post('password'));
        $user->save();

        return redirect()->back()->with('store',true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $totalUser = count(User::where('delete_able',1)->get());
        $userEdit = User::where('slug', $slug)->firstOrFail();
        $user = User::where('delete_able',1)
        ->OrderBy('name','asc')->paginate(10);
    
        // Rest of your code for editing the project...

        return view('user.index', compact('userEdit','user','totalUser'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        // $request->validate(
        //     [
        //     'email' => [
        //         'required',
        //         'email',
        //         Rule::unique('users', 'email')->ignore($user->id),
        //     ],
        //     'phone' => ['nullable','regex:/^(\+62|0|62)[0-9]{9,13}$/'],
        // ]);
        
        $user->name = $request->post('name'); 
        $user->email = $request->post('email');
        $user->phone = $request->post('phone');
        $user->save();

        return redirect()->to(route('user.index'))->with('update',true);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()->with('delete',true);
    }
}
