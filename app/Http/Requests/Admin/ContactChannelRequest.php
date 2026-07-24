<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ContactChannelRequest extends FormRequest {
    public function authorize():bool{return true;}
    public function rules():array{return ['name'=>'required|string|max:100','type'=>['required',Rule::in(['phone','zalo','messenger','email','whatsapp','other'])],'value'=>'required|string|max:255','url'=>'nullable|url|max:2048','icon'=>'nullable|string|max:100','is_primary'=>'nullable|boolean','show_topbar'=>'nullable|boolean','show_footer'=>'nullable|boolean','show_floating'=>'nullable|boolean','is_active'=>'nullable|boolean','sort_order'=>'nullable|integer|min:0'];}
}
