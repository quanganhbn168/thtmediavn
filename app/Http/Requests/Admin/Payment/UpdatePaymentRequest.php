<?php
namespace App\Http\Requests\Admin\Payment;
use Illuminate\Validation\Rule;
class UpdatePaymentRequest extends StorePaymentRequest {public function rules():array{$rules=parent::rules();$rules['payment_code']=['required','string','max:50',Rule::unique('payments','payment_code')->ignore($this->route('payment'))];return $rules;}}
