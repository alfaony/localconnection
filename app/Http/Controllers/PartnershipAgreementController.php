<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

use App\Models\PartnershipAgreement;
use App\Models\PartnershipAgreementType;
use App\Models\SettingCompany;
use App\Models\AgreementSignature;
use App\Schemas\ParamSchema;

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

        foreach (['lampiran_1_image', 'lampiran_2_image', 'lampiran_3_image'] as $lampiranKey) {
            if ($request->hasFile('fields.' . $lampiranKey)) {
                $path = Storage::put('public/images/lampiran', $request->file('fields.' . $lampiranKey));
                $dataArray[$lampiranKey] = $path;
            }
        }

        $data['status'] = ParamSchema::DRAFT;
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

    public function edit($id)
    {
        $partnershipAgreement = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
        $types = PartnershipAgreementType::all();
        $fields = $partnershipAgreement->fields;
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');

        return view('partnership_agreement.createOrEdit', compact('partnershipAgreement','types','fields','company'));
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'partnership_agreement_type_id' => 'required',
            // 'letter_number' => 'required',
            // 'number_result' => 'required',
            // 'status' => 'required|in:draf,submit,onreview,done',
        ]);
        
        $dataArray = $request->fields;
        $partnershipAgreement = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
        if (method_exists($partnershipAgreement, 'signature') && $partnershipAgreement->signature()->exists()) {
            $partnershipAgreement->signature()->delete();
        }
        

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

        foreach (['lampiran_1_image', 'lampiran_2_image', 'lampiran_3_image'] as $lampiranKey) {
            if ($request->hasFile('fields.' . $lampiranKey)) {
                if (!empty($partnershipAgreement->fields[$lampiranKey])) {
                    Storage::delete($partnershipAgreement->fields[$lampiranKey]);
                }
                $path = Storage::put('public/images/lampiran', $request->file('fields.' . $lampiranKey));
                $dataArray[$lampiranKey] = $path;
            }
        }

        $data['is_approve'] = false;
        $data['status'] = ParamSchema::DRAFT;
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

    public function destroy($id)
    {
        $partnershipAgreement = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
        if (!$partnershipAgreement->isPermission('delete')) 
        {
            return back()->withErrors(['agreement' => 'Perjanjian tersebut tidak dapat di hapus']);
        }
        $partnershipAgreement->delete();
        return redirect()->route('partnership-agreement.index')->with('delete', true);
    }

    public function submit($id)
    {
        $partnershipAgreement = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
        if (!$partnershipAgreement->isPermission('submit')) 
        {
            return back()->withErrors(['agreement' => 'Perjanjian tersebut tidak dapat di submit']);
        }
        $data['status'] = 'submit';
        $partnershipAgreement->update($data);
        
        return redirect()->route('partnership-agreement.downloadPdf', $partnershipAgreement->id)->with('submit', true);
    }

    public function signature(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'signature' => 'required|string', // Ensure signature is passed as a string (base64 image)
            'ktp' => 'required|mimes:jpg,png,pdf|max:2048', // Validate KTP file
        ], [
            'signature.required' => 'Tanda tangan harus diisi.',
            'ktp.required' => 'KTP harus diupload.',
            'signature.string' => 'Format tanda tangan tidak valid.',
            'ktp.mimes' => 'KTP hanya bisa berupa JPG, PNG atau PDF.',
            'ktp.max' => 'Ukuran KTP maksimal 2MB.',
        ]);

        DB::beginTransaction();
        try {
            $signature = $request->input('signature'); // Base64 Signature
    
            // Handle the KTP file upload
            $ktp = $request->file('ktp'); // KTP file
    
            // Store the KTP file
            if ($ktp) 
            {
                $ktpPath = $ktp->store('ktps');
            }
            

            // Process and save the signature if it's valid
            if ($signature) {
                $imageData = base64_decode(
                    preg_replace('#^data:image/\w+;base64,#i', '', $signature)
                );

                $signaturePath = 'signatures/' . uniqid() . '.png';

                Storage::disk('s3')->put('public/'.$signaturePath, $imageData);

                $signatureUrl = Storage::disk('s3')->url($signaturePath);
            }
            $partnershipAgreement = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
    
            // Proceed to store the rest of the data (e.g., save the KTP path, signature path, etc.)
            // Example: save the data in the database
            $agreement = new AgreementSignature();
            $agreement->partnership_agreement_id = $id;
            $agreement->signature = $signaturePath;
            $agreement->image_ktp = $ktpPath ?? null;
            $agreement->order = $partnershipAgreement->getNextSignatureNumber();
            $agreement->save();

            $partnershiResent = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
            $partnershiResent->status = $partnershiResent->getApprove() ? ParamSchema::ONREVIEW : ParamSchema::SIGNATURE;
            $partnershiResent->save();
            

            DB::commit();
            return redirect()->route('partnership-agreement.downloadPdf', $partnershipAgreement->id)->with('sign', true);
        } catch (\Throwable $th) 
        {
            //throw $th;
            Log::error($th);
            DB::rollBack();

            return redirect()->route('partnership-agreement.downloadPdf', $partnershipAgreement->id)->with('sign', false);
        }
    }

    public function share($id, Request $request)
    {
        $agreement = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
        
        // Generate a token if it doesn't exist
        if (!$agreement->token) {
            $agreement->token = (string) Str::uuid();
        }

        // Store the password if it's provided, hash it for security
        if ($request->password) 
        {
            $agreement->password = Hash::make($request->password);
        }

        // Save the agreement with the password and token
        $agreement->is_share = true;
        $agreement->save();

        $url = route('partnership-agreement.sharePdf', ['id' => $agreement->id, 'token' => $agreement->token]);
        
        return response()->json([
            'success' => true,
            'message' => 'Document shared successfully.',
            'url' => $url,  // Return the token to the user
        ]);
    }

    public function sharePdf($id, Request $request)
    {
        $token = $request->query('token');
        $agreement = PartnershipAgreement::where('token', $token)->findOrFail($id);

        $attemptsKey = 'share_attempts_' . $id;
        $attempts = session($attemptsKey, 0);

        if ($attempts >= 3) {
            return view('partnership_agreement.share_blocked', compact('agreement'));
        }

        $needsPassword = $agreement->password && !session('share_authenticated_' . $id);

        return view('partnership_agreement.show_share_pdf', compact('agreement', 'needsPassword', 'attempts', 'token'));
    }

    public function verifySharePassword(Request $request, $id)
    {
        $token = $request->query('token');
        $agreement = PartnershipAgreement::where('token', $token)->findOrFail($id);

        $attemptsKey = 'share_attempts_' . $id;
        $attempts = session($attemptsKey, 0);

        if ($attempts >= 3) {
            return response()->json(['blocked' => true, 'success' => false]);
        }

        if (!$agreement->password || !Hash::check($request->password, $agreement->password)) {
            $newAttempts = $attempts + 1;
            session([$attemptsKey => $newAttempts]);
            $remaining = 3 - $newAttempts;

            return response()->json([
                'success' => false,
                'blocked' => $newAttempts >= 3,
                'remaining' => max(0, $remaining),
            ]);
        }

        session(['share_authenticated_' . $id => true]);
        session([$attemptsKey => 0]);

        return response()->json(['success' => true]);
    }

    public function signatureShare(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'password' => 'required|string',
            'signature' => 'required|string', // Ensure signature is passed as a string (base64 image)
            'ktp' => 'required|mimes:jpg,png,pdf|max:2048', // Validate KTP file
        ], [
            'signature.required' => 'Tanda tangan harus diisi.',
            'ktp.required' => 'KTP harus diupload.',
            'signature.string' => 'Format tanda tangan tidak valid.',
            'ktp.mimes' => 'KTP hanya bisa berupa JPG, PNG atau PDF.',
            'ktp.max' => 'Ukuran KTP maksimal 2MB.',
        ]);
        $partnershipAgreement = PartnershipAgreement::findOrFail($id);
        if ($partnershipAgreement->password && !Hash::check($request->password, $partnershipAgreement->password)) 
        {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah. Mohon coba lagi dengan password yang benar.']);
        }

        DB::beginTransaction();
        try {
            $signature = $request->input('signature'); // Base64 Signature
    
            // Handle the KTP file upload
            $ktp = $request->file('ktp'); // KTP file
    
            // Store the KTP file
            if ($ktp) 
            {
                $ktpPath = $ktp->store('ktps');
            }
    
            // Process and save the signature if it's valid
            if ($signature) {
                $imageData = base64_decode(
                    preg_replace('#^data:image/\w+;base64,#i', '', $signature)
                );

                $signaturePath = 'signatures/' . uniqid() . '.png';

                Storage::disk('s3')->put('public/'.$signaturePath, $imageData);

                $signatureUrl = Storage::disk('s3')->url($signaturePath);
            }
            $partnershipAgreement = PartnershipAgreement::findOrFail($id);
    
            // Proceed to store the rest of the data (e.g., save the KTP path, signature path, etc.)
            // Example: save the data in the database
            $agreement = new AgreementSignature();
            $agreement->partnership_agreement_id = $id;
            $agreement->signature = $signaturePath;
            $agreement->image_ktp = $ktpPath ?? null;
            $agreement->order = $partnershipAgreement->getNextSignatureNumber();
            $agreement->save();

            $partnershiResent = PartnershipAgreement::findOrFail($id);
            $partnershiResent->status = $partnershiResent->getApprove() ? 'onreview' : 'signature';
            $partnershiResent->save();
            

            DB::commit();
            return redirect()->back()->with('sign', true);
        } catch (\Throwable $th) 
        {
            //throw $th;
            Log::error($th);
            DB::rollBack();

            return redirect()->back()->with('sign', false);
        }
    }

    public function approvement(Request $request, $id)
    {
        $request->validate([
            'approval_status' => 'required|in:approved,rejected',
        ]);
        $agreement = PartnershipAgreement::byCompany(Auth::user()->company_id)->findOrFail($id);
        $agreement->status = $request->approval_status;
        $agreement->is_approve = $request->approval_status == ParamSchema::APPROVED ? true : false;
        $agreement->reason = $request->reason;
        $agreement->save();

        return redirect()->back()->with($request->approval_status, true);
    }
}
