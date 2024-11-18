<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SettingCompany;
use Illuminate\Support\Facades\DB;


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
        
        DB::beginTransaction();
        try {
            $settings = SettingCompany::byCompany(Auth::user()->company_id)->get();

            foreach ($settings as $setting) 
            {
                $title = $setting->field_title;
                if ($request->has($title)) 
                {
                    $fieldValue = $request->input($title);

                    if ($request->hasFile($title)) {
                        $file = $request->file($title);
                        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $filenameToStore = $filename . '_' . time() . '.' . $extension;

                        $filePath = $file->storeAs('company', $filenameToStore, 'public');
                        $fieldValue = $filePath;
                    }
                    $setting->user_id = Auth::user()->id;
                    $setting->update(['field_value' => $fieldValue]);
                }
            }

            DB::commit();
            return redirect()->route('setting-company.index')->with('store',true);
        } catch (\Throwable $th) {
            dd($th);
            DB::rollback();
            Log::error($th);
            return redirect()->route('setting-company.index')->with('store',false);
        }
    }
}
