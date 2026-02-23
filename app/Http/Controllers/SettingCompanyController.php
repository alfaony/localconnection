<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SettingCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class SettingCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $agreementTemplate = config('custom.agreementTemplate');
        return view('setting_company.createOrEdit',compact('data','agreementTemplate'));
    }

   

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => ['nullable', 'string'],
                'address' => ['nullable', 'string'],
                'npwp_number' => ['nullable', 'string'],
                'director' => ['nullable', 'string'],
                'currency' => ['nullable', 'string'],
                'currency_usd' => ['nullable', 'numeric'],
                'nib_file' => ['nullable', 'file', 'max:2048'],
                'acta_file' => ['nullable', 'file', 'max:2048'],
                'npwp_file' => ['nullable', 'file', 'max:2048'],
            ]);
        if(!$request->has('status_punihsment_task_doing'))
        {
            $request->request->add(['status_punihsment_task_doing' => "0"]);
        }
        
        // Clear Cache
        $this->clearCache("midtrans",Auth::user()->company_id);
        $this->clearCache("xendit",Auth::user()->company_id);
        $this->clearCache("xendit_software_subscription",Auth::user()->company_id);
        $this->clearCache("payment_gateway",Auth::user()->company_id);
        $this->clearCache("payment_gateway_settings",Auth::user()->company_id);

        
        // Boolean
        $boolean = ['xendit_pay_with_ppn','midtrans_pay_with_ppn','manual_payment_status','xendit_pay_with_ppn_software_subscription','software_sharing_manual_payment_status'];
        foreach ($boolean as $field) {
            $request->request->add([$field => $request->has($field) ? "1" : "0"]);
        }

        DB::beginTransaction();
        try {
            $settings = SettingCompany::byCompany(Auth::user()->company_id)->get();
            $arrayExsist = ['header_store_image'];
            $cacheKey = "xendit_settings_".Auth::user()->company_id;
            Cache::forget($cacheKey);
            foreach ($settings as $setting) 
            {
                $title = $setting->field_title;
                if ($request->has($title) && !in_array($title, $arrayExsist)) 
                {
                    $fieldValue = $request->input($title);
                    if ($request->hasFile($title)) {
                        $file = $request->file($title);
                        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $filenameToStore = $filename . '_' . time() . '.' . $extension;

                        $filePath = $file->storeAs('company', $filenameToStore);
                        $fieldValue = $filePath;
                    }
                    $setting->user_id = Auth::user()->id;
                    $setting->update(['field_value' => $fieldValue]);
                }
                if ($request->has($title) && in_array($title, $arrayExsist)) 
                {
                    $fieldValue = $request->input($title);
                    if ($request->hasFile($title)) {
                        $file = $request->file($title);
                        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $filenameToStore = $filename . '_' . time() . '.' . $extension;

                        $filePath = $file->storeAs('company_storage_file', $filenameToStore);
                        $fieldValue = $filePath;
                    }
                    $setting->user_id = Auth::user()->id;
                    $setting->update(['field_value' => $fieldValue]);
                }
            }

            DB::commit();
            return redirect()->route('setting-company.index')->with('store',true);
        } catch (\Throwable $th) {
            DB::rollback();
            \Log::error($th);
            return redirect()->route('setting-company.index')->with('store',false);
        }
    }

    /**
     * Clear Cache
     */
    public static function clearCache($menu,$companyId)
    {
        Cache::forget("{$menu}_settings_{$companyId}");
    }
}
