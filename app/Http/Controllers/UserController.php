<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Schemas\RoleSchema;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
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
        $company = Company::orderBy('name','asc')->get();
        $companyAccess = false;
        $roleAccess = false;

        if((Auth::user()->role->name != RoleSchema::ADMIN && Auth::user()->role->name != RoleSchema::ROOT))
        {
            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::byCompany(Auth::user()->company_id)->where('delete_able',1)
                ->where('email','like', '%' . $request->get('email') . '%')
                ->where('id',Auth::user()->id)
                ->OrderBy('name','asc')->paginate(10);
            $totalUser = User::byCompany(Auth::user()->company_id)->where('delete_able',1)->where('id',Auth::user()->id)->count();

        }
        elseif(Auth::user()->role->name == RoleSchema::ADMIN )
        {
            $roleAccess = true;

            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::where('delete_able',1)
            ->byCompany(Auth::user()->company_id)
            ->where('email','like', '%' . $request->get('email') . '%')
            ->OrderBy('name','asc')->paginate(10);
            $totalUser = User::byCompany(Auth::user()->company_id)->where('delete_able',1)->count();
        }
        else
        {       
            $companyAccess = true;
            $roleAccess = true;

            $role = Role::get();
            $user = User::where('email','like', '%' . $request->get('email') . '%')
                    ->OrderBy('name','asc')->paginate(10);
            $totalUser = User::where('delete_able',1)->count();
        }


        return view('user.index',compact('user','totalUser','role','company', 'companyAccess', 'roleAccess'));
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
        $user->role_id = $request->post('role') ?? Auth::user()->role_id;
        $user->company_id = $request->post('company') ?? Auth::user()->company_id;
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
        $userEdit = User::where('slug', $slug)->firstOrFail();
        $company = Company::orderBy('name','asc')->get();

        $companyAccess = false;
        $roleAccess = false;

        
        if((Auth::user()->role->name != RoleSchema::ADMIN && Auth::user()->role->name != RoleSchema::ROOT))
        {
            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::where('delete_able',1)
                ->byCompany(Auth::user()->company_id)
                ->where('id',Auth::user()->id)
                ->OrderBy('name','asc')->paginate(10);
            $totalUser = User::where('delete_able',1)->byCompany(Auth::user()->company_id)->where('id',Auth::user()->id)->count();

        }
        elseif(Auth::user()->role->name == RoleSchema::ADMIN )
        {
            $roleAccess = true;

            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::where('delete_able',1)
            ->byCompany(Auth::user()->company_id)
            ->OrderBy('name','asc')->paginate(10);
             $totalUser = User::byCompany(Auth::user()->company_id)->where('delete_able',1)->count();
        }
        else
        {   
            if($userEdit->role->name != RoleSchema::ROOT)
            {
                $companyAccess = true;
                $roleAccess = true;
            }
            
            $role = Role::get();

            $user = User::where('delete_able',1)
            ->OrderBy('name','asc')->paginate(10);
            $totalUser = User::where('delete_able',1)->count();
        }
    

        return view('user.index', compact('userEdit','user','totalUser','role', 'company', 'companyAccess', 'roleAccess'));
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
        $user->role_id = $request->post('role') ?? $user->role_id;

        if($request->post('newPassword'))
        {
            $user->password = bcrypt($request->post('newPassword'));
        }
        
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
