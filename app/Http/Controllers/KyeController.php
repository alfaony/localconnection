<?php

namespace App\Http\Controllers;

use App\Models\Kye;
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
            
            $data['user_id'] = Auth::user()->id;
            $data['approval_status'] = 'pending';
            $kye = Kye::create($data);
    
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
        return view('kye.createOrEdit', compact('kye'));
    }
    
    /**
     * SHow
     */
    public function show($id)
    {
        $kye = KYE::findOrFail($id);
        return view('kye.show', compact('kye'));
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

            // Update data ke database
            $data['approval_status'] = 'pending';

            $kye->update($data);

            return redirect()->route('kye.show', $kye)->with('success', 'Data KYE berhasil diperbarui.');
        } catch (\Throwable $th) {
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

}