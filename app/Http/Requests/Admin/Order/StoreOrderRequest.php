<?php

namespace App\Http\Requests\Admin\Order;

use App\Http\Requests\Admin\Order\UpdateOrderRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends UpdateOrderRequest
{
    public function rules(): array
    {
        return [
            'order_code' => ['required', 'string', 'max:50', Rule::unique('orders', 'order_code')],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:100'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'order_type' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:pending,processing,confirmed,completed,cancelled'],
            'payment_status' => ['required', 'string', 'in:unpaid,partial,paid,refunded'],
            'subtotal_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'remaining_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'note' => ['nullable', 'string'],
            'admin_note' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'items' => ['nullable', 'array'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string'],
        ];
    }
}
