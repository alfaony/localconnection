<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Schemas\RoleSchema;

use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Division;
use App\Models\UserStatus;

use App\Rules\MatchOldPassword;
use Carbon\Carbon;
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
        $divisions = Division::byCompany(Auth::user()->company_id)->get();
        $dayofweek = config('custom.daysOfWeek');
        
        if(Auth::user()->role->name == RoleSchema::ADMIN || Auth::user()->role->name == RoleSchema::HR)
        {
            $roleAccess = true;

            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::where('delete_able',1)
            ->byCompany(Auth::user()->company_id)
            ->where('email','like', '%' . $request->get('email') . '%')
            ->OrderBy('name','asc')->paginate(10);
            $users = User::byCompany(Auth::user()->company_id)->get();

            $totalUser = User::byCompany(Auth::user()->company_id)->where('delete_able',1)->count();
        }
        elseif((Auth::user()->role->name != RoleSchema::ADMIN && Auth::user()->role->name != RoleSchema::ROOT))
        {
            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::byCompany(Auth::user()->company_id)->where('delete_able',1)
                ->where('email','like', '%' . $request->get('email') . '%')
                ->where('id',Auth::user()->id)
                ->OrderBy('name','asc')->paginate(10);
            $users = User::byCompany(Auth::user()->company_id)->get();

            $totalUser = User::byCompany(Auth::user()->company_id)->where('delete_able',1)->where('id',[Auth::user()->id])->count();

        }
        else
        {
            $companyAccess = true;
            $roleAccess = true;

            $role = Role::get();
            $user = User::where('email','like', '%' . $request->get('email') . '%')
                    ->OrderBy('name','asc')->paginate(10);
            $users = User:: get();

            $totalUser = User::where('delete_able',1)->count();
        }

        return view('user.index',compact('user','totalUser','role','company', 'companyAccess', 'roleAccess','users', 'divisions', 'dayofweek'));
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
        $user->approvement_user_id = $request->post('approvement_user_id') ?? NULL;

        // Checkin
        $user->is_checkin = $request->post('is_checkin', 0); // Default 0 jika tidak dicentang
        $user->manual_checkin = $request->post('manual_checkin', 0);
        $user->requires_photo = $request->post('requires_photo', 0);
        $user->requires_location = $request->post('requires_location', 0);
        $user->start_time = $request->post('start_time');
        $user->end_time = $request->post('end_time');
        $user->rest_time = $request->post('rest_time');
        $user->end_rest_time = $request->post('end_rest_time');

        if ($request->has('custom_rest_times')) 
        {
            $user->custom_rest_times = $request->custom_rest_times;
        }

        $user->save();


        $divisions = $request->input('divisions');
        if ($divisions) {
            $user->divisions()->attach($divisions);
        }
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
        $divisions = Division::byCompany(Auth::user()->company_id)->get();
        $divisionsUser = $userEdit->divisions ? $userEdit->divisions->pluck('id')->toArray() : NULL ;
        $dayofweek = config('custom.daysOfWeek');


        $companyAccess = false;
        $roleAccess = false;


        if(Auth::user()->role->name == RoleSchema::ADMIN || Auth::user()->role->name == RoleSchema::HR)
        {
            $roleAccess = true;

            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::where('delete_able',1)
            ->byCompany(Auth::user()->company_id)
            ->OrderBy('name','asc')->paginate(10);
            $users = User::byCompany(Auth::user()->company_id)->get();

            $totalUser = User::byCompany(Auth::user()->company_id)->where('delete_able',1)->count();
        }
        elseif((Auth::user()->role->name != RoleSchema::ADMIN && Auth::user()->role->name != RoleSchema::ROOT))
        {
            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::where('delete_able',1)
                ->byCompany(Auth::user()->company_id)
                ->where('id',Auth::user()->id)
                ->OrderBy('name','asc')->paginate(10);
            $totalUser = User::where('delete_able',1)->byCompany(Auth::user()->company_id)->where('id',Auth::user()->id)->count();
            $users = User::byCompany(Auth::user()->company_id)->get();

        }
        else
        {
            // if($userEdit->role->name != RoleSchema::ROOT)
            // {
                $companyAccess = true;
                $roleAccess = true;
            // }

            $role = Role::get();
            $users = User::get();

            $user = User::where('delete_able',1)
            ->OrderBy('name','asc')->paginate(10);
            $totalUser = User::where('delete_able',1)->count();
        }


        return view('user.index', compact('userEdit','user','totalUser','role', 'company', 'companyAccess', 'roleAccess','users', 'divisions','divisionsUser', 'dayofweek'));
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
        $user->name = $request->post('name');
        $user->email = $request->post('email');
        $user->phone = $request->post('phone');
        $user->role_id = $request->post('role') ?? $user->role_id;
        $user->approvement_user_id = $request->post('approvement_user_id') ?? NULL;

        if($request->post('newPassword'))
        {
            $user->password = bcrypt($request->post('newPassword'));
        }

        $divisions = $request->input('divisions');
        $user->divisions()->sync($divisions);

        // Checkin
        $user->is_checkin = $request->post('is_checkin', 0); // Default 0 jika tidak dicentang
        $user->manual_checkin = $request->post('manual_checkin', 0);
        $user->requires_photo = $request->post('requires_photo', 0);
        $user->requires_location = $request->post('requires_location', 0);
        $user->start_time = $request->post('start_time');
        $user->end_time = $request->post('end_time');
        $user->rest_time = $request->post('rest_time');
        $user->end_rest_time = $request->post('end_rest_time');

        if ($request->has('custom_rest_times')) 
        {
            $user->custom_rest_times = $request->custom_rest_times;
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

    /**
     * User Profile edit
     */
    public function profileEdit($slug)
    {
        $userEdit = User::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $userApps = UserStatus::where('user_id', $userEdit->id)->orderby('last_scheduled_checkin','desc')->paginate(5);

        
        if($userEdit->id != Auth::user()->id)
        {
            return redirect()->back();
        }
        return view('user.edit', compact('userEdit','userApps'));
    }

    /**
     * User Profile update
     */
    public function profileUpdate(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable','regex:/^(\+62|0|62)[0-9]{9,13}$/'],
            'oldPassword' => ['nullable', new MatchOldPassword],
        ]);

        $user = User::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        if($user->id != Auth::user()->id)
        {
            return redirect()->back();
        }
        $user->name = $request->post('name');
        $user->phone = $request->post('phone');
        $user->address = $request->post('address');
        $user->id_card = $request->post('id_card');
        $user->npwp_number = $request->post('npwp_number');

        if ($request->hasFile('id_card_image')) 
        {
            $file = $request->file('id_card_image');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = 'public/id_cards/' . $fileName;

            // Simpan file ke storage
            Storage::put($filePath, file_get_contents($file));

            // Update kolom id_card_image di tabel users
            // $user = Auth::user();
            $user->id_card_image = $filePath;
        }

        if($request->post('oldPassword'))
        {
            $request->validate([
                'newPassword' => 'min:6',
                'confirmPassword' => 'same:newPassword',
            ],
            [
                'newPassword.required' => 'Password baru harus diisi.',
                'newPassword.min' => 'Password baru minimal 6 karakter.',
                'confirmPassword.required' => 'Konfirmasi password harus diisi.',
                'confirmPassword.same' => 'Konfirmasi password harus sama dengan password.',
            ]
        );

            $user->password = bcrypt($request->post('newPassword'));
        }

        $user->save();

        return redirect()->to(route('home'))->with('updateProfile',true);   
    }

    public function updatefcm(Request $request)
    {
        // Validasi input
        $request->validate([
            'token' => 'required|string',
        ]);

        // Dapatkan user yang sedang login
        try 
        {
            $user = Auth::user();
            $userStatus = UserStatus::where('user_id', $user->id)->where('fcm_id', $request->token)->first();
        
            if (!$userStatus) 
            {
                $userStatus = new UserStatus();
                $userStatus->user_id = $user->id;
                $userStatus->browser_name = $request->browser_name;
                $userStatus->fcm_id = $request->token;
                $userStatus->is_online = 1;
                $userStatus->last_login_at = Carbon::now();
            } else {
                $userStatus->browser_name = $request->browser_name;
                $userStatus->fcm_id = $request->new_token ?? $request->token;
                $userStatus->is_online = 1;
                $userStatus->last_login_at = Carbon::now();
            }
            $userStatus->save();
            
            return response()->json(['message' => 'FCM ID updated successfully.'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return response()->json(['message' => 'Failed to update FCM ID.'], 500);
        }
    }
    
}
