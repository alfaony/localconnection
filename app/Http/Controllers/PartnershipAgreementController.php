<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Models\PartnershipAgreement;
use App\Models\PartnershipAgreementType;
use App\Models\SettingCompany;

class PartnershipAgreementController extends Controller
{
    public function index()
    {
        $search = request()->query('search');
        $agreements = PartnershipAgreement::byCompany(Auth::user()->company_id)->when($search, function($query, $search) {
            return $query->where(function ($query) use ($search) {
                $query->whereHas('updateCreate', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('type', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('number_result', 'like', "%{$search}%");
            });
        })->orderBy('created_at', 'desc')->paginate(10);
        
        return view('partnership_agreement.index', compact('agreements'));
    }

    public function create()
    {
        $types = PartnershipAgreementType::all();
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        return view('partnership_agreement.createOrEdit', compact('types','company'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'partnership_agreement_type_id' => 'required',
            // 'letter_number' => 'required',
            // 'number_result' => 'required',
            // 'status' => 'required|in:draf,submit,onreview,done',
            // 'fields' => 'required|array',
        ]);

        $letter_number = PartnershipAgreement::byCompany(Auth::user()->company_id)->withTrashed()->max('letter_number') + 1;
        $date = Carbon::now()->format('m/Y');
        $numberResult = $letter_number.'/'.$date;
        
        $dataArray = $request->fields;

        if ($request->hasFile('fields.image_topologi')) 
        {
            $imageTopologi = Storage::put('public/images/topologi', $request->file('fields.image_topologi'));
            $dataArray['image_topologi'] = $imageTopologi;
        }

        if ($request->hasFile('fields.image_bast')) 
        {
            $imageBast = Storage::put('public/images/bast', $request->file('fields.image_bast'));
            $dataArray['image_bast'] = $imageBast;
        }


        $data['letter_number'] = $letter_number;
        $data['date_agreement'] = Carbon::parse($request->date_agreement)->format('Y-m-d');
        $data['number_result'] = $numberResult;
        $data['fields'] = json_encode($dataArray);
        $data['company_id'] = Auth::user()->company_id;
        $data['user_created_id'] = Auth::user()->id;
        $data['user_updated_id'] = Auth::user()->id;

        $partnershipAgreement = PartnershipAgreement::create($data);

        return redirect()->route('partnership-agreement.downloadPdf', $partnershipAgreement->id)->with('store', true);
    }

    public function edit(PartnershipAgreement $partnershipAgreement)
    {
        $types = PartnershipAgreementType::all();
        $fields = $partnershipAgreement->fields;
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');

        return view('partnership_agreement.createOrEdit', compact('partnershipAgreement','types','fields','company'));
    }
    public function update(Request $request, PartnershipAgreement $partnershipAgreement)
    {
        $data = $request->validate([
            'partnership_agreement_type_id' => 'required',
            // 'letter_number' => 'required',
            // 'number_result' => 'required',
            // 'status' => 'required|in:draf,submit,onreview,done',
        ]);

        $dataArray = $request->fields;

        if ($request->hasFile('fields.image_topologi')) 
        {
            if (!empty($partnershipAgreement->fields['image_topologi'])) {
                Storage::delete($partnershipAgreement->fields['image_topologi']);
            }
            $imageTopologi = Storage::put('public/images/topologi', $request->file('fields.image_topologi'));
            $dataArray['image_topologi'] = $imageTopologi;
        }

        if ($request->hasFile('fields.image_bast')) 
        {
            if (!empty($partnershipAgreement->fields['image_bast'])) 
            {
                Storage::delete($partnershipAgreement->fields['image_bast']);
            }
            $imageBast = Storage::put('public/images/bast', $request->file('fields.image_bast'));
            $dataArray['image_bast'] = $imageBast;
        }


        $data['fields'] = json_encode($dataArray);
        $data['user_updated_id'] = Auth::user()->id;

        $partnershipAgreement->update($data);

        return redirect()->route('partnership-agreement.downloadPdf', $partnershipAgreement->id)->with('update', true);
    }

    public function downloadPdf($id)
    {
        $agreement = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
        return view('partnership_agreement.show_pdf', compact('agreement'));
    }

    public function destroy(PartnershipAgreement $partnershipAgreement)
    {
        $partnershipAgreement->delete();
        return redirect()->route('partnership-agreement.index')->with('delete', true);
    }
}
