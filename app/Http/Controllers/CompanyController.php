<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Schemas\RoleSchema;

use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Models\SettingCompany;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::paginate(10);
        $agreementTemplate = config('custom.agreementTemplate');
        return view('company.index',compact('company', 'agreementTemplate'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompanyRequest $request)
    {
        DB::beginTransaction();
        try {
            //code...
            $company = new Company();
            $company->name = $request->post('company_name');
            $company->save();
    
            $role = Role::where('name',RoleSchema::ADMIN)->first();
    
            $user = new User();
            $user->name = $request->post('name'); 
            $user->email = $request->post('email');
            $user->phone = $request->post('phone');
            $user->role_id = $role->id;
            $user->password = bcrypt($request->post('password'));
            $user->company_id = $company->id;
            $user->save();
    
            $fieldProfile = ['name' => $request->post('company_pt'),'director'=> $request->post('director'),'address' => $request->post('address'),'npwp_number' => $request->post('npwp_number'),'currency'=>'','currency_usd'=>"",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => '','template_perjanjian' => $request->post('template_perjanjian')];
            $fieldEmail = ['clock_in' => '08:00','reward_point_conversion' => '500','late_point'=>-10,'on_time_poin'=>0,'host' => '','port' => '','username' => '','password' => '','encryption'=> '','sent_time'=>'','sent_time_status'=>''];
    
            foreach ($fieldProfile as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            foreach ($fieldEmail as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            DB::commit();
            return redirect()->back()->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollback();
            Log::error($th);
            return redirect()->back()->with('store',false);
        }

    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        $companyEdit = $company;
        $company = Company::paginate(10);
        return view('company.index',compact('company','companyEdit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(CompanyRequest $request, Company $company)
    {
        $company->name = $request->post('company_name');
        $company->save();

        return redirect()->to(route('company.index'))->with('update',true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->back()->with('delete',true);
    }
}
