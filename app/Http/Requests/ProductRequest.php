<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'price_buy' => 'nullable|numeric',
            'price_sell' => 'required|numeric',
            'method_count' => 'required|string|max:255',
            'product_category_id' => 'nullable|exists:product_categories,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'price_buy.required' => 'Harga beli wajib diisi.',
            'price_buy.numeric' => 'Harga beli harus berupa angka.',
            'price_sell.required' => 'Harga jual wajib diisi.',
            'price_sell.numeric' => 'Harga jual harus berupa angka.',
            'method_count.required' => 'Metode perhitungan wajib diisi.',
            'product_category_id.exists' => 'Kategori produk tidak valid.',
        ];
    }
}
