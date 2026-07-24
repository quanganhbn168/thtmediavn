<?php
namespace App\Http\Requests\Admin\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreCustomerRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['name'=>['required','string','max:255'],'phone'=>['nullable','string','max:50'],'email'=>['nullable','email','max:100'],'address'=>['nullable','string','max:255'],'birthday'=>['nullable','date','before:today'],'gender'=>['nullable',Rule::in(['male','female','other'])],'notes'=>['nullable','string'],'is_active'=>['nullable','boolean']];} }
