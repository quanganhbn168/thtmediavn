<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['pending', 'processing', 'shipping', 'completed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'paid', 'partial', 'refunded'])],
            'per_page' => ['nullable', 'integer', 'in:10,20,25,50'],
        ];
    }
}

