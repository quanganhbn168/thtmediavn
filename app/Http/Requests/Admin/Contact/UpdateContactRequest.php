<?php
namespace App\Http\Requests\Admin\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateContactRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['status'=>['required',Rule::in(['new','read','processing','replied','spam'])],'admin_notes'=>['nullable','string']];}}
