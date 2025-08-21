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
use App\Models\DayoffType;
use App\Models\DayoffQuota;

use App\Helpers\Access;

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
        $dayoffTypes = DayoffType::all();
        
        if(Auth::user()->role->name == RoleSchema::ROOT)
        {
            $companyAccess = true;
            $roleAccess = true;

            $role = Role::get();
            $user = User::where(function($query) use ($request) {
                        $query->where('email', 'like', '%' . $request->get('email') . '%')
                              ->orWhere('name', 'like', '%' . $request->get('email') . '%');
                    })
                    ->orderBy('name', 'asc')
                    ->paginate(10);
            $users = User:: get();

            $totalUser = User::where('delete_able',1)->count();
        }else
        {
            $roleAccess = true;

            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::where('delete_able',1)
            ->byCompany(Auth::user()->company_id)
            ->where(function($query) use ($request) {
                $query->where('email','like', '%' . $request->get('email') . '%')
                    ->orWhere('name','like', '%' . $request->get('email') . '%');
            })
            ->OrderBy('name','asc')->paginate(10);
            $users = User::byCompany(Auth::user()->company_id)->get();

            $totalUser = User::byCompany(Auth::user()->company_id)->where('delete_able',1)->count();
        }

        return view('user.index',compact('user','totalUser','role','company', 'companyAccess', 'roleAccess','users', 'divisions', 'dayofweek', 'dayoffTypes'));
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
        $user->email_gmail = $request->post('email_gmail');
        $user->phone = $request->post('phone');
        $user->role_id = $request->post('role') ?? Auth::user()->role_id;
        $user->company_id = $request->post('company') ?? Auth::user()->company_id;
        $user->password = bcrypt($request->post('password'));
        $user->approvement_user_id = $request->post('approvement_user_id') ?? NULL;

        $user->use_ip_restriction = $request->post('use_ip_restriction', 0);
        $user->ip_addresses = $request->has('ip_addresses') ? $request->ip_addresses : NULL;
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
        
        if($request->quotas)
        {
            $user->dayoff_active = $request->dayoff_active ? true : false;

            foreach ($request->quotas as $typeId => $jumlah) 
            {
                $type = DayoffType::find($typeId);            
                DayoffQuota::updateOrCreate(
                    ['user_id' => $user->id, 'dayoff_type_id' => $typeId],
                    ['quota' => $jumlah]
                );
            }

            $user->save();
        }

        // $divisions = $request->input('divisions');
        // if ($divisions) {
        //     $user->divisions()->attach($divisions);
        // }
        $divisionIds = $request->input('divisions', []);

        // Ambil divisi yang dicentang sebagai wajib laporan
        $weeklyRequired = $request->input('weekly_report_required', []);

        // Siapkan data pivot
        $syncData = [];
        foreach ($divisionIds as $divisionId) {
            $syncData[$divisionId] = [
                'weekly_report_required' => isset($weeklyRequired[$divisionId])
            ];
        }

        // Simpan ke pivot division_user
        $user->divisions()->sync($syncData);
        
        if (!empty($request->company_access)) {
            $user->accessibleCompanies()->sync($request->company_access);
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
        $weeklyRequired = $userEdit->divisions->filter(function ($d) {
            return $d->pivot->weekly_report_required;
        })->pluck('id')->toArray() ?? [];
        $dayofweek = config('custom.daysOfWeek');
        $dayoffTypes = DayoffType::all();
        $userQuotas = $userEdit->dayoffQuotas->pluck('quota', 'dayoff_type_id')->toArray() ?? [];



        $companyAccess = false;
        $roleAccess = false;


        if(Auth::user()->role->name == RoleSchema::ROOT)
        {
            $companyAccess = true;
            $roleAccess = true;

            $role = Role::get();
            $user = User::OrderBy('name','asc')->paginate(10);
            $users = User:: get();

            $totalUser = User::where('delete_able',1)->count();
        }else
        {
            $roleAccess = true;

            $role = Role::where('name','!=',RoleSchema::ROOT)->get();
            $user = User::where('delete_able',1)
            ->byCompany(Auth::user()->company_id)
            ->OrderBy('name','asc')->paginate(10);
            $users = User::byCompany(Auth::user()->company_id)->get();

            $totalUser = User::byCompany(Auth::user()->company_id)->where('delete_able',1)->count();
        }


        return view('user.index', compact('userEdit','user','totalUser','role', 'company', 'companyAccess', 'roleAccess','users', 'divisions','divisionsUser', 'dayofweek','dayoffTypes','userQuotas', 'weeklyRequired'));
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
        $user->email_gmail = $request->post('email_gmail');
        $user->phone = $request->post('phone');
        $user->role_id = $request->post('role') ?? $user->role_id;
        $user->approvement_user_id = $request->post('approvement_user_id') ?? NULL;

        if($request->post('newPassword'))
        {
            $user->password = bcrypt($request->post('newPassword'));
        }

        // $divisions = $request->input('divisions');
        // $user->divisions()->sync($divisions);
        $divisionIds = $request->input('divisions', []);

        // Ambil input divisi yang wajib isi laporan
        $weeklyRequired = $request->input('weekly_report_required', []); // array key: division_id

        // Persiapkan data sinkronisasi pivot
        $syncData = [];
        foreach ($divisionIds as $divisionId) {
            $syncData[$divisionId] = [
                'weekly_report_required' => isset($weeklyRequired[$divisionId]) // true if checked
            ];
        }

        // Sync relasi many-to-many + pivot
        $user->divisions()->sync($syncData);

        $user->use_ip_restriction = $request->post('use_ip_restriction', 0);
        $user->ip_addresses = $request->has('ip_addresses') ? $request->ip_addresses : NULL;

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

        $dayoffTypes = DayoffType::all();

        if($request->quotas)
        {
            $user->dayoff_active = $request->dayoff_active ? true : false;

            foreach ($request->quotas as $typeId => $jumlah) 
            {
                $type = DayoffType::find($typeId);            
                DayoffQuota::updateOrCreate(
                    ['user_id' => $user->id, 'dayoff_type_id' => $typeId],
                    ['quota' => $jumlah]
                );
            }
        }

        $user->save();

        // Update akses tambahan
        $user->accessibleCompanies()->sync($request->company_access ?? []);

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
        $permissionEditProfile = Access::can('edit_profile_all_user','users');
        
        if(($userEdit->id != Auth::user()->id) && !$permissionEditProfile)
        {
            return redirect()->back();
        }
        return view('user.edit', compact('userEdit','userApps','permissionEditProfile'));
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
            'background' => 'nullable|string',
            'experience' => 'nullable|string',
            'skill' => 'nullable|string',
            'achievement' => 'nullable|array',
            'failure' => 'nullable|array',
        ]);

        $user = User::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $permissionEditProfile = Access::can('edit_profile_all_user','users');

        
        if($user->id != Auth::user()->id && !$permissionEditProfile)
        {
            return redirect()->back();
        }
        $user->name = $request->post('name');
        $user->phone = $request->post('phone');
        $user->address = $request->post('address');
        $user->id_card = $request->post('id_card');
        $user->npwp_number = $request->post('npwp_number');
        $user->email_gmail = $request->post('email_gmail');

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

        if ($request->hasFile('avatar')) 
        {
            $fileAvatar = $request->file('avatar');
            $fileNameAvatar = uniqid() . '.' . $fileAvatar->getClientOriginalExtension();
            $filePathAvatar = 'public/avatars/' . $fileNameAvatar;

            // Simpan file ke storage
            Storage::put($filePathAvatar, file_get_contents($fileAvatar));

            // Update kolom id_card_image di tabel users
            // $user = Auth::user();
            $user->avatar = $filePathAvatar;
        }
        
        // **Update Background, Experience, Skill**
        $user->background = $request->post('background');
        $user->experience = $request->post('experience');
        $user->skill = $request->post('skill');

        // **Achievement & Failure**
        $newAchievements = $request->post('achievement', []);
        $newFailures = $request->post('failure', []);

        // Ambil data lama dari database
        $existingAchievements = json_decode($user->achievement, true) ?? [];
        $existingFailures = json_decode($user->failure, true) ?? [];

        if (Access::can('edit_profile_all_user','users')) 
        {
            // Jika ROOT atau ADMIN, izinkan update penuh
            $user->achievement = json_encode($newAchievements);
            $user->failure = json_encode($newFailures);
        } else {
            // Role lain hanya boleh menambahkan, tidak bisa menghapus
            $user->achievement = json_encode(array_unique(array_merge($existingAchievements, $newAchievements)));
            $user->failure = json_encode(array_unique(array_merge($existingFailures, $newFailures)));
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

        if(Access::can('edit_profile_all_user','users') && $user->id != Auth::user()->id)
        {
            return redirect()->to(route('user.index'))->with('update',true);
        }else
        {
            return redirect()->to(route('home'))->with('updateProfile',true);   
        }
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
