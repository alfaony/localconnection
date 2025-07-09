<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Schemas\RoleSchema;

use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Models\SettingCompany;
use App\Models\AssetType;

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
    
            $fieldProfile = ['name' => $request->post('company_pt'),'director'=> $request->post('director'),'address' => $request->post('address'),'npwp_number' => $request->post('npwp_number'),'currency'=>'','currency_usd'=>"",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => '','template_perjanjian' => $request->post('template_perjanjian'),'affiliate_company'=>null,'closed_time'=>"14:00"];
            $fieldEmail = ['clock_in' => '08:00','reward_point_conversion' => '500','late_point'=>-10,'on_time_poin'=>0,'host' => '','port' => '','username' => '','password' => '','encryption'=> '','sent_time'=>'','sent_time_status'=>''];
            $fieldHeadLetter = ['header' => '', 'footer' => ''];
            $fieldXero = ['client_id' => '', 'client_secret' => '', 'webhook_key' => ''];
            $fieldBank = ['rekening_number' => null,'atas_nama' => null,'nama_bank' => null,'cabang_bank' => null];

            // non Setting
            $Assetfields = ['Kartu Akses','⁠Kunci gembok','Kunci pintu','Kunci motor','Kunci mobil','Kunci lemari','Kunci brangkas','Kunci ruangan','kunci Lain'];

            $fieldPunishmentTaskDoing = ['status_punihsment_task_doing' => null, 'point_punishment_task_doing' => null];
            $fieldPunishment = ['point_punishment_task_todo' => null, 'point_punishment_weekly_report' => null];
            $fieldWablas = ['server_wablas' => null,'token_wablas' => null, 'webhook_key_wablas' => null];
            $fieldGoogle = ['google_client_id' => null,'google_client_secret' => null, 'google_redirect_uri' => null, 'google_refresh_token' => null, 'google_access_token' => null,'google_expires_at' => null , 'google_token_created_at' => null];
    
            foreach ($fieldProfile as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="profile";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            foreach ($fieldEmail as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="email";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            foreach ($fieldHeadLetter as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="asset_head_letter";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            foreach ($fieldXero as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="xero";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }
            
            foreach ($fieldBank as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="bank";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            foreach ($fieldPunishmentTaskDoing as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="punishment_task_doing";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            foreach ($fieldPunishment as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="punishment";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            foreach ($fieldWablas as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="wablas";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }
            
            foreach ($fieldGoogle as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->menu="google";
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            foreach ($Assetfields as $key => $value) 
            {
                $asset = new AssetType();
                $asset->name = $value;
                $asset->user_id = $user->id;
                $asset->save();
            }
            
            $this->saveMasterCheck($company->id);            

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

    protected function saveMasterCheck($companyId)
    {
        $components = [
            'Monitor',
            'Keyboard',
            'Battery',
            'Camera',
            'Charger',
            'Mouse Pad',
            'Body',
            'Speaker',
            'Wifi',
        ];

        $itemComponents = [
          'Kondisi fisik',
          '⁠Kondisi nyala',
          '⁠Kondisi kardus', 
          '⁠Kondisi perlengkapan',  
        ];

        foreach ($components as $component) 
        {
            $masterCheckItem = \App\Models\MasterCheckItem::where('company_id', $companyId)->where('name', $component)->first();
            if (!$masterCheckItem) {
                \App\Models\MasterCheckItem::create([
                    'company_id' => $companyId,
                    'name' => $component,
                ]);
            }

            foreach ($itemComponents as $itemComponent) 
            {
                $masterCheck = \App\Models\MasterCheck::where('company_id', $companyId)->where('name', $itemComponent)->first();
                if (!$masterCheck) {
                    \App\Models\MasterCheck::create([
                        'company_id' => $companyId,
                        'name' => $itemComponent,
                        'type' => 'item_type',
                    ]);
                }
            }
        }
    }
}
