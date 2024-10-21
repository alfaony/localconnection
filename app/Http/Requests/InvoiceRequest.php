<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:sales_date',
            'tax' => 'nullable|numeric|min:0|max:100',
            'service_fee' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric',
            'charges' => 'nullable|numeric',
            'product' => 'required|array',
            'description' => 'required|array',
            'qty' => 'required|array',
            'price' => 'required|array',
            'sub_total' => 'required|array',
            'product.*' => 'required|string|exists:products,id', // Contoh validasi jika product adalah ID dari tabel products
            'description.*' => 'required|string',
            'qty.*' => 'required|integer|min:1',
            'price.*' => 'nullable|integer|min:1',
            'sub_total.*' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'start_date.required' => 'Mulai diperlukan.',
            'start_date.date' => 'Mulai harus berupa tanggal.',
            'end_date.required' => 'Selesai diperlukan.',
            'end_date.date' => 'Selesai harus berupa tanggal.',
            'end_date.after_or_equal' => 'Selesai harus setelah atau sama dengan tanggal mulai.',
            'customer.required' => 'Pelanggan diperlukan.',
            'customer.exists' => 'Pelanggan tidak ditemukan.',
            'leads_from.required' => 'Leads diperlukan.',
            'leads_from.in' => 'Leads tidak valid.',
            'division_budget.exists' => 'Anggaran divisi tidak ditemukan.',
            'date.required' => 'Tanggal wajib diisi.',
            'tax.required' => 'Pajak diperlukan.',
            'tax.numeric' => 'Pajak harus dalam format angka.',
            'tax.min' => 'Pajak minimal 0%.',
            'tax.max' => 'Pajak maksimal 100%.',
            'service_fee.required' => 'Biaya layanan diperlukan.',
            'service_fee.numeric' => 'Biaya layanan harus dalam format angka.',
            'service_fee.min' => 'Biaya layanan minimal 0%.',
            'service_fee.max' => 'Biaya layanan maksimal 100%.',
            'product.required' => 'Produk diperlukan.',
            'product.*.required' => 'Produk diperlukan.',
            'product.*.string' => 'Produk harus berupa string.',
            'product.*.exists' => 'Produk tidak ditemukan.',
            'description.required' => 'Deskripsi diperlukan.',
            'description.*.required' => 'Deskripsi diperlukan.',
            'description.*.string' => 'Deskripsi harus berupa string.',
            'qty.required' => 'Kuantitas diperlukan.',
            'qty.*.required' => 'Kuantitas diperlukan.',
            'qty.*.integer' => 'Kuantitas harus berupa angka bulat.',
            'qty.*.min' => 'Kuantitas minimal adalah 1.',
            'sub_total.required' => 'Sub total diperlukan.',
            'sub_total.*.required' => 'Sub total diperlukan.',
            'sub_total.*.numeric' => 'Sub total harus dalam format angka.',
            'sub_total.*.min' => 'Sub total minimal adalah 0.',
        ];
    }
}
