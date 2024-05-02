<?php

namespace App\Http\Controllers;

use App\Models\CctvCheck;
use App\Models\CctvCheckPhoto;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use Carbon\Carbon;

class CctvCheckController extends Controller
{
    public function index(Request $request)
    {   
        $query = CctvCheck::query();
        if ($request->has('date') && $request->date != '') 
        {
            $query->whereDate('date', '=', $request->date);
        }

        if ($request->task == NULL || $request->task == 'today') 
        {
            $query->whereDate('date', '=', Carbon::now());
        }
    
        if ($request->has('status') && $request->status != '') {
            $query->whereHas('taskStatus', function ($q) use ($request) {
                $q->where('name', '=', $request->status);
            });
        }
    
        if ($request->has('user') && $request->user != '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user . '%');
            });
        }
        $checks = $query->byCompany(Auth::user()->company_id)->with('photos')->orderby('created_at','desc')->paginate(10);
        $users = User::byCompany(Auth::user()->company_id)->byRole(RoleSchema::OB)->get(); 

        return view('cctv_check.index', compact('checks', 'users'));
    }

    public function create()
    {
        return view('cctv_check.createOrEdit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos.*' => 'required|image|max:10240', // 10MB Max
        ]);

        $today = Carbon::now();

        DB::beginTransaction();
        try {
            $check = new CctvCheck();
            $check->user_id = auth()->id();
            $check->date = $today;
            $check->time = $today->format('H:i:s');
            $check->save();
    
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $file = $photo;
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('security', $filename, 'public');
                    
                    $photoCheck = new CctvCheckPhoto();
                    $photoCheck->cctv_check_id = $check->id;
                    $photoCheck->path = $path;
                    $photoCheck->save();
                }
            }
            
            DB::commit();
            return redirect()->route('cctv-check.index')->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->route('cctv-check.index')->with('store',false);
        }
    }

    public function show($slug)
    {
        $check = CctvCheck::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $photos = $check->photos;

        return view('cctv_check.show', compact('check', 'photos'));
    }

    public function edit($slug)
    {
        $check = CctvCheck::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        return view('cctv_check.createOrEdit',compact('check'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'photos.*' => 'required|image|max:10240', // 10MB Max
        ]);

        $today = Carbon::now();

        DB::beginTransaction();
        try {
            $check = CctvCheck::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
            $check->user_id = auth()->id();
            $check->date = $today;
            $check->clock_out = $today->format('H:i:s');
            $check->save();

    
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $file = $photo;
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('security', $filename, 'public');
                    
                    $photoCheck = new CctvCheckPhoto();
                    $photoCheck->cctv_check_id = $check->id;
                    $photoCheck->path = $path;
                    $photoCheck->status_of_day = ParamSchema::CHECKOUT;
                    $photoCheck->save();
                }
            }
            
            DB::commit();
            return redirect()->route('cctv-check.index')->with('update',true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->route('cctv-check.index')->with('update',false);
        }
    }
    public function destroy($slug)
    {
        $check = CctvCheck::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $check->delete();

        return redirect()->route('cctv-check.index')->with('delete',true);
    }
    
}

