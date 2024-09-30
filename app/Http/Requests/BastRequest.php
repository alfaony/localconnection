<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BastRequest extends FormRequest
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
        return 
        [
            'date' => 'required|date',
            // 'work_order' => 'required|uuid|exists:work_orders,id',
            'project' => 'required|uuid|exists:projects,id',
            'number_purchase' => 'required|string',
            'pic' => 'required|string',
        ];
    }

    public function messages()
    {
        return 
        [
            'date.required' => 'Tanggal diperlukan.',
            'work_order.required' => 'Work Order diperlukan.',
            'project.required' => 'Project diperlukan.',
            'number_purchase.required' => 'Nomor Pembelian diperlukan.',
            'pic.required' => 'PIC diperlukan.',
        ];
    }
}
