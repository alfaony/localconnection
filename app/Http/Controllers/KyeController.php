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
            Kye::create($data);
    
            return redirect()->route('kye.show', $kye)->with('success', 'Data KYE berhasil ditambahkan.');
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
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
            //code...
            if ($request->employee_photo) {
                $data['employee_photo'] = $this->saveBase64Image($request->employee_photo, 'employee_photos');
            }
            if ($request->ktp_photo) {
                $data['ktp_photo'] = $this->saveBase64Image($request->ktp_photo, 'ktp_photos');
            }
            if ($request->selfie_ktp) {
                $data['selfie_ktp'] = $this->saveBase64Image($request->selfie_ktp, 'selfie_ktp_photos');
            }
            if ($request->ktp_family) {
                $data['ktp_family'] = $this->saveBase64Image($request->ktp_family, 'ktp_family_photos');
            }
            if ($request->house_photo) {
                $data['house_photo'] = $this->saveBase64Image($request->house_photo, 'house_photos');
            }
    
            $kye->update($data);
    
            return redirect()->route('kye.show', $kye)->with('success', 'Data KYE berhasil diperbarui.');
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
        }
    }

    // Delete KYE Record
    public function destroy($id)
    {
        $kye = KYE::findOrFail($id);
        $kye->delete();

        return redirect()->route('kye.create')->with('success', 'KYE record deleted successfully.');
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