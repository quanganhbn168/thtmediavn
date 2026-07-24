<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequestSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'status' => ['required', Rule::in(['new', 'contacted', 'proposal_sent', 'won', 'lost'])],
            'admin_notes' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:255'],
            'service_purpose_id' => ['nullable', 'exists:service_purposes,id'],
        ];
    }
}
