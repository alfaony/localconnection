<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use App\Schemas\ParamSchema;

use App\Models\IpRight;
use App\Models\User;


class IpRightController extends Controller
{
    public function index(Request $request)
    {
        $query = IpRight::query();

        if($request->status)
        {
            $query->where('status',$request->status);
        }

        if ($request->has('user') && $request->user != '') 
        {
            $query->whereHas('user', function ($q) use ($request) 
            {
                $q->where('name', 'like', '%' . $request->user . '%');
            });
        }

        $users = User::byCompany(Auth::user()->company_id)->get(); // Ambil semua user, bisa disesuaikan
        $ipRights = $query->byUserAndApproval(Auth::user()->id)->orderBy('created_at','desc')->paginate(10);
        $status = config('custom.statusApproval');

        return view('ipright.index', compact('ipRights','status', 'users'));
    }

    public function create()
    {
        return view('ipright.createOrEdit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'patent_date' => 'required|date',
            'patent_number' => 'required|string',
            'description' => 'required',
            'file_path' => 'required|file|mimes:pdf|max:2048'
        ]);



        $ipRight = new IpRight();

        $ipRight->name = $request->name;
        $ipRight->user_id = Auth::user()->id;
        $ipRight->approval_user_id = Auth::user()->id;
        $ipRight->status = ParamSchema::INREVIEW;
        $ipRight->patent_date =  $request->patent_date;
        $ipRight->patent_number = $request->patent_number;
        $ipRight->description =  $request->description;

        if ($request->hasFile('file_path'))
        {
            $file = $request->file('file_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('parents', $filename, 'public');
            $ipRight->file_path= 'parents/' . $filename;
        }

        $ipRight->save();

        return redirect()->route('ip-right.index')->with('success', 'Hak Cipta Berhasil Ditambahkan.');
    }

    public function show($slug)
    {
        $ipRight = IpRight::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        return view('ipright.show', compact('ipRight'));
    }

    public function edit($slug)
    {
        $ipRight = IpRight::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        return view('ipright.createOrEdit', compact('ipRight'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required',
            'patent_date' => 'required|date',
            'patent_number' => 'required|string',
            'description' => 'required',
            'file_path' => 'nullable|file|mimes:pdf|max:2048'
        ]);

        $ipRight = IpRight::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        if ($request->hasFile('file_path'))
        {
            if($ipRight->file_path){Storage::delete($ipRight->file_path);}

            $file = $request->file('file_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('parents', $filename, 'public');
            $ipRight->file_path= 'parents/' . $filename;
        }

        $ipRight->name = $request->name;
        $ipRight->patent_date =  $request->patent_date;
        $ipRight->patent_number = $request->patent_number;
        $ipRight->description =  $request->description;
        $ipRight->point =  $request->point ?? NULL;
        $ipRight->save();

        return redirect()->route('ip-right.index')->with('success', 'Hak Cipta Berhasil Diperbarui.');
    }

    public function destroy($slug)
    {
        $ipRight = IpRight::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        if($ipRight->file_path){Storage::delete($ipRight->file_path);}

        $ipRight->delete();

        return redirect()->route('ip-right.index')->with('success', 'Hak Cipta Berhasil Dihapus.');
    }

    public function addpoint(Request $request, $slug)
    {
        $request->validate([
            'point' => 'required|integer',
        ]);

        $ipRight = IpRight::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $ipRight->approval_user_id = Auth::user()->id;
        $ipRight->point = $request->point;
        $ipRight->status = ParamSchema::COMPLATE;
        $ipRight->approved = true;
        $ipRight->save();


        return redirect()->route('ip-right.show',$ipRight->slug)->with('success', 'Point Hak Itelektual Berhasil Ditambahkan.');
    }
}
