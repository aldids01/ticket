<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_name' => ['required'],
            'issue_type' => ['required'],
            'description' => ['required'],
            'status' => ['required'],
            'mikrotik_user' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
