<?php

namespace App\Http\Controllers;

use App\Models\SecurityCheck;
use App\Models\SecurityCheckPhoto;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Schemas\ParamSchema;
use App\Helpers\Access;
use Carbon\Carbon;

class SecurityCheckController extends Controller
{
    public function index()
    {
        $checks = SecurityCheck::byCompany(Auth::user()->company_id)->with('photos')->orderby('created_at','desc')->paginate(10);
        $today = SecurityCheck::byCompany(Auth::user()->company_id)->where('date',Carbon::now()->format('Y-m-d'))->first();

        // permission
        $isShow = Access::can('show','security_checks');
        $isDestroy = Access::can('destroy','security_checks');



        return view('security_check.index', compact('checks','today', 'isShow', 'isDestroy'));
    }

    public function create()
    {
        return view('security_check.createOrEdit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos.*' => 'required|image|max:10240', // 10MB Max
            'descriptions.*' => 'required|string|max:225', 
        ]);

        $today = Carbon::now();

        DB::beginTransaction();
        try {
            $check = new SecurityCheck();
            $check->user_id = auth()->id();
            $check->date = $today;
            $check->clock_in = $today->format('H:i:s');
            $check->save();
            
            $description = $request->post('descriptions');

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $key => $photo) {
                    $file = $photo;
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('security', $filename);
                    
                    $photoCheck = new SecurityCheckPhoto();
                    $photoCheck->security_check_id = $check->id;
                    $photoCheck->description = $description[$key];
                    $photoCheck->path = $path;
                    $photoCheck->status_of_day = ParamSchema::CHECKIN;
                    $photoCheck->save();
                }
            }
            
            DB::commit();
            return redirect()->route('security-check.index')->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->route('security-check.index')->with('store',false);
        }
    }

    public function show($slug)
    {
        $type = request('type') == ParamSchema::CHECKOUT ? ParamSchema::CHECKOUT : ParamSchema::CHECKIN;

        $securityCheck = SecurityCheck::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $photos = $securityCheck->photos->where('status_of_day',$type);

        return view('security_check.show', [
            'securityCheck' => $securityCheck,
            'photos' => $photos,
            'type' => $type  // Pass type to view for additional context or UI logic
        ]);
    }

    public function edit($slug)
    {
        $check = SecurityCheck::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        return view('security_check.createOrEdit',compact('check'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'photos.*' => 'required|image|max:10240', // 10MB Max
            'descriptions.*' => 'required|string|max:225', 
        ]);

        $today = Carbon::now();

        DB::beginTransaction();
        try {
            $check = SecurityCheck::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
            $check->user_id = auth()->id();
            $check->date = $today;
            $check->clock_out = $today->format('H:i:s');
            $check->save();

            $description = $request->post('descriptions');
    
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $key => $photo) {
                    $file = $photo;
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('security', $filename);
                    
                    $photoCheck = new SecurityCheckPhoto();
                    $photoCheck->security_check_id = $check->id;
                    $photoCheck->description = $description[$key];
                    $photoCheck->path = $path;
                    $photoCheck->status_of_day = ParamSchema::CHECKOUT;
                    $photoCheck->save();
                }
            }
            
            DB::commit();
            return redirect()->route('security-check.index')->with('update',true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->route('security-check.index')->with('update',false);
        }
    }
    public function destroy($slug)
    {
        $check = SecurityCheck::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $check->delete();

        return redirect()->route('security-check.index')->with('delete',true);
    }
    
}
