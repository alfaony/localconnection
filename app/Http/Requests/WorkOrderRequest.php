<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkOrderRequest extends FormRequest
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
        if ($this->isMethod('post')) 
        {
            return 
            [
                'date' => 'required|date',
                'quote' => 'required|uuid|exists:quotes,id',
                'product.*' => 'required|string|max:255',
                'description.*' => 'required|string',
                'qty.*' => 'required|numeric|min:1',
                'ids.*' => 'nullable|uuid|exists:work_order_products,id',
                // 'sub_total.*' => 'required|numeric|min:0',
                'quote_file' => 'required|file|mimes:pdf', // Ini adalah contoh validasi untuk file PDF dengan maksimum 2MB.
            ];
        }

        return [
            'date' => 'required|date',
            'quote' => 'required|uuid|exists:quotes,id',
            'product.*' => 'required|string|max:255',
            'description.*' => 'required|string',
            'qty.*' => 'required|numeric|min:1',
            'ids.*' => 'nullable|uuid|exists:work_order_products,id',
            // 'sub_total.*' => 'required|numeric|min:0',
            'quote_file' => 'nullable|file|mimes:pdf', // Ini adalah contoh validasi untuk file PDF dengan maksimum 2MB.
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'Tanggal harus diisi.',
            'quote.required' => 'Quote harus dipilih.',
            'product.*.required' => 'Produk harus diisi.',
            'description.*.required' => 'Deskripsi harus diisi.',
            'qty.*.required' => 'Jumlah produk harus diisi dan minimal 1.',
            'sub_total.*.required' => 'Sub total harus diisi dan minimal 0.',
            'quote_file.required' => 'File Surat Penawaran diperlukan.',
            'quote_file.file' => 'Harap unggah file yang valid.',
            'quote_file.mimes' => 'File harus dalam format PDF.',
            'quote_file.max' => 'Ukuran file maksimal adalah 2MB.',
        ];
    }
}
