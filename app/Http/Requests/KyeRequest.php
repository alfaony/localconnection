<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Base64Image;

class KyeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = $this->method() === 'PUT';

        $rules = [
            'employee_photo' => [$isUpdate ? 'nullable' : 'required', new Base64Image()],
            'ktp_photo' => [$isUpdate ? 'nullable' : 'required', new Base64Image()],
            'selfie_ktp' => [$isUpdate ? 'nullable' : 'required', new Base64Image()],
            'ktp_family' => [$isUpdate ? 'nullable' : 'required'],
            'house_photo' => [$isUpdate ? 'nullable' : 'required', new Base64Image()],
            'full_name' => 'required|string|max:255',
            'birth_place' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'address' => 'required|string',
            'ktp_number' => 'required|string|max:20',
            'npwp_number' => 'nullable|string|max:20',
            'google_maps' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'imei_number' => 'nullable|string|max:50',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'email' => ':attribute harus berupa email yang valid.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'in' => ':attribute tidak valid.',
            'unique' => ':attribute sudah digunakan.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'base64image' => ':attribute harus berupa gambar valid dalam format Base64.',
        ];
    }

    public function attributes()
    {
        return [
            'full_name' => 'Nama Lengkap',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'address' => 'Alamat',
            'employee_photo' => 'Foto Karyawan',
            'ktp_number' => 'Nomor KTP',
            'ktp_photo' => 'Foto KTP',
            'selfie_ktp' => 'Selfie dengan KTP',
            'ktp_family' => 'Foto KTP Orang Tua/Saudara',
            'npwp_number' => 'Nomor NPWP',
            'google_maps' => 'Sharelokasi Rumah',
            'house_photo' => 'Foto Rumah',
            'phone_number' => 'Nomor Telepon',
            'email' => 'Email',
            'imei_number' => 'Kode IMEI',
            'emergency_phone' => 'Nomor Telepon Darurat',
            'emergency_contact' => 'Nama Kontak Darurat',
            'bank_account_name' => 'Nama Rekening Bank',
            'bank_name' => 'Nama Bank',
            'account_number' => 'Nomor Rekening',
            'approval_status' => 'Status Approval',
            'approval_note' => 'Catatan Approval',
        ];
    }
}
