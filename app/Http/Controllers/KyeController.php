<?php

namespace App\Http\Controllers;

use App\Models\Kye;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\KyeRequest;
use Illuminate\Support\Facades\Storage;
class KyeController extends Controller
{
    // Show Create Form
    public function index(Request $request)
    {
        $search = $request->input('search');

        $kyeRecords = Kye::when($search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ktp_number', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('kye.index', compact('kyeRecords'));
    }

    public function create()
    {
        if(Auth::user()->kye) 
        {
            return redirect()->route('kye.show', Auth::user()->kye->id)->with('error', 'Catatan KYE sudah ada.');
        }

        return view('kye.createOrEdit');
    }

    // Store KYE Data
    public function store(KyeRequest $request)
    {
        $data = $request->validated();

        try {
            // Save the Base64 images as files using Storage
            if ($request->employee_photo) {
                $data['employee_photo'] = $this->saveBase64ImageToStorage($request->employee_photo, 'employee_photos');
            }
            if ($request->ktp_photo) {
                $data['ktp_photo'] = $this->saveBase64ImageToStorage($request->ktp_photo, 'ktp_photos');
            }
            if ($request->selfie_ktp) {
                $data['selfie_ktp'] = $this->saveBase64ImageToStorage($request->selfie_ktp, 'selfie_ktp_photos');
            }
            if ($request->ktp_family) {
                $data['ktp_family'] = $this->saveBase64ImageToStorage($request->ktp_family, 'ktp_family_photos');
            }
            if ($request->house_photo) {
                $data['house_photo'] = $this->saveBase64ImageToStorage($request->house_photo, 'house_photos');
            }
            
            if ($request->skck) 
            {
                $data['skck'] = $request->file('skck')->store('skck_files');
            }
            
            if ($request->skck) 
            {
                $data['skck'] = 'skck_files/' . uniqid() . '.' . $request->file('skck')->extension();
                Storage::put('public/' . $data['skck'], file_get_contents($request->file('skck')->getRealPath()));
            }

            $data['user_id'] = Auth::user()->id;
            $data['approval_status'] = 'pending';
            $kye = Kye::create($data);
            
            $user = User::findOrFail($kye->user_id);

            if($request->ktp_number)
            {
                $user->id_card = $data['ktp_number'];
            }
            
            if($request->ktp_photo) 
            {
                $user->id_card_image = $data['ktp_photo'];
            }

            if($request->address) 
            {
                $user->address = $data['address'];
            }

            if($request->npwp_number) 
            {
                $user->npwp_number = $data['npwp_number'];
            }

            if($request->npwp_number) 
            {
                $user->npwp_number = $data['npwp_number'];
            }
            
            $user->save(); 
            return redirect()->route('kye.show', $kye)->with('success', 'Data KYE berhasil ditambahkan.');
        } catch (\Throwable $th) {
            //throw $th;
            return redirect()->route('kye.create')->with('error', 'Terjadi kesalahan saat menyimpan data KYE.');
        }
    }

    // Show Edit Form
    public function edit($id)
    {
        $kye = KYE::findOrFail($id);
        if(!$kye->isEdit())
        {
            return redirect()->route('kye.show',$kye->id)->with('error', 'KYE Tidak dapat di Ubah.');
        }
        return view('kye.createOrEdit', compact('kye'));
    }
    
    /**
     * SHow
     */
    public function show($id)
    {
        $kye = KYE::findOrFail($id);
        $status = config('custom.status_kye');

        return view('kye.show', compact('kye','status'));
    }

    // Update KYE Data
    public function update(KyeRequest $request, Kye $kye)
    {
        $data = $request->validated();

        try {
            // Cek apakah ada input baru untuk setiap foto, jika tidak, gunakan foto yang sudah ada
            $data['employee_photo'] = $request->employee_photo
                ? $this->saveBase64ImageToStorage($request->employee_photo, 'employee_photos')
                : $kye->employee_photo;

            $data['ktp_photo'] = $request->ktp_photo
                ? $this->saveBase64ImageToStorage($request->ktp_photo, 'ktp_photos')
                : $kye->ktp_photo;

            $data['selfie_ktp'] = $request->selfie_ktp
                ? $this->saveBase64ImageToStorage($request->selfie_ktp, 'selfie_ktp_photos')
                : $kye->selfie_ktp;

            $data['ktp_family'] = $request->ktp_family
                ? $this->saveBase64ImageToStorage($request->ktp_family, 'ktp_family_photos')
                : $kye->ktp_family;

            $data['house_photo'] = $request->house_photo
                ? $this->saveBase64ImageToStorage($request->house_photo, 'house_photos')
                : $kye->house_photo;
            if ($request->skck) 
            {
                if (isset($kye->skck)) 
                {
                    Storage::delete('public/' . $kye->skck);
                }
                
                $data['skck'] = 'skck_files/' . uniqid() . '.' . $request->file('skck')->extension();
                Storage::put('public/' . $data['skck'], file_get_contents($request->file('skck')->getRealPath()));
            }

            $user = User::findOrFail($kye->user_id);
            // Update data ke database
            $data['approval_status'] = 'pending';
            
            $kye->update($data);
            
            if($request->ktp_number)
            {
                $user->id_card = $data['ktp_number'];
            }
            
            if($request->ktp_photo) 
            {
                $user->id_card_image = $data['ktp_photo'];
            }

            if($request->address) 
            {
                $user->address = $data['address'];
            }

            if($request->npwp_number) 
            {
                $user->npwp_number = $data['npwp_number'];
            }

            if($request->npwp_number) 
            {
                $user->npwp_number = $data['npwp_number'];
            }
            $user->save(); 
            
            
            return redirect()->route('kye.show', $kye)->with('success', 'Data KYE berhasil diperbarui.');
        } catch (\Throwable $th) {
            // dd($th);
            // Log error untuk debugging
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data KYE.');
        }
    }


    // Delete KYE Record
    public function destroy($id)
    {
        $kye = KYE::byCompany(Auth::user()->company_id)->findOrFail($id);
        $kye->delete();

        return redirect()->back()->with('success', 'KYE record deleted successfully.');
    }

    public function approvement(Request $request, Kye $kye)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        try {
            $kye->update(['approval_status' => $request->status, 'approval_note' => $request->approval_note]);

            $message = $request->status === 'approved' 
                ? 'Aktivasi berhasil disetujui.' 
                : 'Aktivasi berhasil ditolak.';

            return redirect()->route('kye.show', $kye)->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('kye.show', $kye)->with('error', 'Terjadi kesalahan saat mengubah status.');
        }
    }   

    //**Verivy Email */
    public function verifyemail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $currentUserId = $request->input('current_user_id');

        // Cek apakah email sudah digunakan oleh user lain
        $exists = Kye::where('email', $email)->byCompany(Auth::user()->company_id)
            ->when($currentUserId, function ($query) use ($currentUserId) {
                $query->where('id', '!=', $currentUserId);
            })
            ->exists();

        if ($exists) 
        {
            return response()->json(['message' => 'Email sudah digunakan.'], 422);
        }

        return response()->json(['message' => 'Email tersedia.'], 200);
    }
    protected function saveBase64ImageToStorage($base64Image, $folder)
    {
        $fileName = uniqid() . '.png';

        // Decode Base64 image
        $imageData = base64_decode(str_replace(['data:image/png;base64,', ' '], ['', '+'], $base64Image));

        // Use Storage facade to save the file in the public directory
        $filePath = "$folder/$fileName";
        Storage::put("public/$filePath", $imageData);

        return $filePath; // Return the file path as is
    }

    private function sendNotification($reportProject, $timeNotify, $companyId, $approval = null,  $notes = null)
    {
        $data = [
            'work_order' => $reportProject->workOrder->number_result,
            'project' => $reportProject->project->title,
            'user_create' => $reportProject->userCreate->name,
            'created_at' => Carbon::parse($reportProject->created_at)->format('d-m-Y'),
            'notes' => $notes
        ];
        
        $toEmails = [];
        $toUserId = [];
        $toNames = [];
        
        if(!$approval)
        {
            $ccEmails = [Auth::user()->email];
            $usersAdmin = User::where('company_id',Auth::user()->company_id)->whereHas('role', function($q){
                $q->where('name',RoleSchema::ADMIN);
            })->get();

            if($usersAdmin->isEmpty())
            {
                return false;
            }

            $lead = User::byCompany(Auth::user()->company_id)->where('id',Auth::user()->approvement_user_id)->first();
            foreach ($usersAdmin as $user) 
            {
                $toEmails[] = $user->email;
                $toUserId[] = $user->id;
                $toNames[] = $user->name;
            }

            if($lead) $toEmails[] = $lead->email;
        }else
        {
            $toEmails[] = $reportProject->userCreate->email;
            $toUserId[] = $reportProject->userCreate->id;
            $toNames[] = $reportProject->userCreate->name;
            $ccEmails = [Auth::user()->email];
        }

        $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $fromEmail = $smtpConfig['username'] ?? '';
        $fromName = $smtpConfig['name'] ?? '';

        $directUrl = route('report-project.show', $reportProject->slug);
        $data['url'] = $directUrl;

        switch ($timeNotify) 
        {
            case "store":
                $subject = 'Laporan Proyek Baru untuk Persetujuan';
                $tamplate = 'email.notif_create_report_project';

                $this->sentInbox($toUserId,$subject, $directUrl);
                break;

            case "update":
                $subject = 'Notifikasi Pembaruan Laporan Proyek – Revisi Telah Diupload';
                $tamplate = 'email.notif_create_report_project';

                $this->sentInbox($toUserId,$subject, $directUrl);
                break;

            case "approve":
                $subject = 'Anggaran '.$budget->name.' Disetujui';
                $tamplate = 'email.notif_budget_approval';
                $this->sentInbox($toUserId,$subject, $directUrl);
                break;

            case "notapprove":
                $subject = 'Laporan Proyek – Revisi Diperlukan';
                $tamplate = 'email.notif_decline_report_project';

                $this->sentInbox($toUserId,$subject, $directUrl);
                break;
        }

        $data['title'] = $subject;
        
        // Email Helper Notification
         EmailNotifHelper::sentEmail(
            $fromEmail,
            $fromName,
            $toEmails, 
            $toNames, 
            $subject,
            $tamplate,
            $data, 
            $smtpConfig, 
            $companyId, 
            $ccEmails
        );
    }

    private function sentInbox($to,$message,$directUrl)
    {
        foreach ($to as $key => $value) 
        {
            $inboxHelper = new InboxHelper();
            $inboxHelper->sent(
                $value, 
                Auth::user()->id, 
                $message, 
                $directUrl
            );
        }

        return;
    }

}