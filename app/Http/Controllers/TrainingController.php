<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Schemas\ParamSchema;

use App\Models\Training;
use App\Models\Skill;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $query = Training::query();

        if($request->status)
        {
            $query->where('status',$request->status);
        }

        $trainings = $query->byUserAndApproval(Auth::user()->id)->orderBy('created_at','desc')->paginate(10);
        $status = config('custom.statusApproval');

        return view('training.index', compact('trainings','status'));
    }

    public function create()
    {
        $skills = Skill::get();
        return view('training.createOrEdit',compact('skills'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'skills_mastered' => 'required|array',
            'skills_mastered.*' => 'required|string|distinct|min:1',
            'certification_date' => 'required|date',
            'certification_number' => 'required|string|max:255',
            'certification_file' => 'required|file|mimes:pdf|max:2048'
        ]);

        $skillsIds = $this->manageSkills($request->skills_mastered);

        // Upload file sertifikasi
        $training = new Training();
        $training->name = $request->name;
        $training->skills_mastered = $skillsIds;
        $training->user_id = Auth::user()->id;
        $training->status = ParamSchema::INREVIEW;
        $training->certification_date = $request->certification_date;
        $training->certification_number = $request->certification_number;

        if ($request->hasFile('certification_file'))
        {
            $file = $request->file('certification_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('certifications', $filename, 'public');
            $training->certification_file= 'certifications/' . $filename;
        }

        $training->save();
        
        return redirect()->route('training.index')->with('success', 'Training Berhasil Ditambahkan.');
    }

    public function show($slug)
    {
        $training = Training::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $approval = $training->user->approver->id == Auth::user()->id ? TRUE : FALSE ;

        $skillIds = $training->skills_mastered; // Asumsi skills_mastered adalah array ID
        $skills = Skill::findMany($skillIds)->pluck('name', 'id');

        return view('training.show', compact('training','approval','skills'));
    }

    public function edit($slug)
    {
        $training = Training::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $skills = Skill::get();

        return view('training.createOrEdit', compact('training','skills'));
    }

    public function update(Request $request, $slug)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'skills_mastered' => 'required|array',
            'skills_mastered.*' => 'required|string|distinct|min:1',
            'certification_date' => 'required|date',
            'certification_number' => 'required|string|max:255',
            'certification_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $training = Training::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        $skillsIds = $this->manageSkills($request->skills_mastered);

        $training->name = $request->name;
        $training->skills_mastered = $skillsIds;
        $training->user_id = Auth::user()->id;
        $training->status = ParamSchema::INREVIEW;
        $training->certification_date = $request->certification_date;
        $training->certification_number = $request->certification_number;

        if ($request->hasFile('certification_file')) {
            $file = $request->file('certification_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('certifications', $filename, 'public');
            $request->certification_file = 'certifications/' . $filename;
        }

        $training->save();

        return redirect()->route('training.index')->with('success', 'Training Berhasil Diperbarui.');
    }

    public function destroy($slug)
    {
        $training = Training::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $training->delete();
        return redirect()->route('training.index')->with('success', 'Training Berhasil Dihapus.');
    }

    public function addpoint(Request $request, $slug)
    {
        $request->validate([
            'point' => 'required|integer',
        ]);

        $training = Training::ByCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $training->approval_user_id = Auth::user()->id;
        $training->point = $request->point;
        $training->status = ParamSchema::COMPLATE;
        $training->approved = true;
        $training->save();


        return redirect()->route('training.show',$training->slug)->with('success', 'Point Training Berhasil Ditambahkan.');
    }

    protected function manageSkills($skillsInput)
    {
        $skillIds = [];
        foreach ($skillsInput as $skillName)
        {
            $skill = Skill::firstOrCreate(['name' => trim($skillName)]);
            $skillIds[] = $skill->id;
        }
        return $skillIds;
    }
}
