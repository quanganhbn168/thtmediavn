<?php
namespace App\Http\Requests\Admin\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePaymentRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['order_id'=>'required|exists:orders,id','payment_code'=>['required','string','max:50',Rule::unique('payments','payment_code')],'amount'=>'required|numeric|min:0','method'=>'required|in:cash,bank_transfer,vnpay,momo,zalopay','status'=>'required|in:pending,completed,failed,refunded','transaction_id'=>'nullable|string|max:100','payment_date'=>'nullable|date_format:Y-m-d\TH:i','note'=>'nullable|string'];}}
