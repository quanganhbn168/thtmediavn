<?php
namespace App\Http\Requests\Admin\Subscriber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subscriber = $this->route('subscriber');

        return [
            'email' => ['nullable', 'email', 'max:150', 'required_without:phone', Rule::unique('subscribers', 'email')->ignore($subscriber)],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email', Rule::unique('subscribers', 'phone')->ignore($subscriber)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
