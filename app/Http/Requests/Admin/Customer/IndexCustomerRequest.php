<?php
namespace App\Http\Requests\Admin\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class IndexCustomerRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['search'=>['nullable','string','max:100'],'status'=>['nullable',Rule::in(['active','inactive'])],'gender'=>['nullable',Rule::in(['male','female','other'])],'sort'=>['nullable',Rule::in(['newest','name','orders'])],'direction'=>['nullable',Rule::in(['asc','desc'])],'per_page'=>['nullable','integer',Rule::in([10,25,50])]];} }
