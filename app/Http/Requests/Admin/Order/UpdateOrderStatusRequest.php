<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending_payment', 'pending', 'processing', 'shipping', 'completed', 'cancelled', 'payment_expired'])],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
