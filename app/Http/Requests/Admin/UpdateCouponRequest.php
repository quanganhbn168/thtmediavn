<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = null;
        $coupon = $this->route('coupon');
        if ($coupon instanceof Coupon) {
            $couponId = $coupon->id;
        }

        return [
            'code' => ['required', 'string', 'max:60', Rule::unique('coupons', 'code')->ignore($couponId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['fixed', 'percent', 'free_shipping'])],
            'value' => ['required_unless:type,free_shipping', 'nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:product_categories,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Mã giảm giá',
            'name' => 'Tên chương trình',
            'type' => 'Loại giảm giá',
            'value' => 'Giá trị',
            'max_discount' => 'Giảm tối đa',
            'minimum_order' => 'Đơn tối thiểu',
            'usage_limit' => 'Tổng lượt dùng',
            'usage_limit_per_user' => 'Lượt dùng mỗi khách',
            'starts_at' => 'Ngày bắt đầu',
            'ends_at' => 'Ngày kết thúc',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã giảm giá.',
            'code.unique' => 'Mã giảm giá này đã tồn tại.',
            'type.required' => 'Vui lòng chọn loại giảm giá.',
            'type.in' => 'Loại giảm giá không hợp lệ.',
            'value.required_unless' => 'Vui lòng nhập giá trị giảm cho loại giảm giá đã chọn.',
        ];
    }
}
