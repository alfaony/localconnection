<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MomStoreRequest extends FormRequest
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

    public function rules()
    {
        return [
            'mom_date' => ['required', 'date'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'meeting_id' => ['nullable', 'exists:meetings,id'],
            'notes' => ['nullable', 'string'],
            'agendas' => ['required', 'array', 'min:1'],
            'agendas.*.title' => ['required', 'string'],
            'agendas.*.discussion_notes' => ['nullable', 'string'],
            'agendas.*.tasks' => ['nullable', 'array'],
            'agendas.*.tasks.*.title' => ['required', 'string'],
            'agendas.*.tasks.*.end_date' => ['nullable', 'date'],
            'agendas.*.tasks.*.user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'agendas.*.tasks.*.external_email' => ['nullable', 'email'],
        ];
    }
}
