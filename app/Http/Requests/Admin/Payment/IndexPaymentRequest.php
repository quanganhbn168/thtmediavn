<?php

namespace App\Http\Requests\Admin\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'method' => ['nullable', Rule::in(['cash', 'sepay_qr', 'manual_bank_transfer'])],
            'status' => ['nullable', Rule::in(['pending', 'completed', 'failed', 'refunded'])],
            'per_page' => ['nullable', 'integer', 'in:10,25,50'],
        ];
    }
}
