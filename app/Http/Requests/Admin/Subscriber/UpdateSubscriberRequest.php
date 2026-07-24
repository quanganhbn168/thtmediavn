<?php
namespace App\Http\Requests\Admin\Subscriber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateSubscriberRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['email'=>['required','email','max:150',Rule::unique('subscribers','email')->ignore($this->route('subscriber'))],'is_active'=>['nullable','boolean']];}}
