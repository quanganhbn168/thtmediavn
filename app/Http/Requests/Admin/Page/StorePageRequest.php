<?php
namespace App\Http\Requests\Admin\Page;
use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePageRequest extends FormRequest {
    use HasTranslatableValidation;
    public function authorize(): bool { return true; }
    public function rules(): array { return $this->applyTranslatableRules(['template'=>['required','string','max:100'],'published_at'=>['nullable','date_format:Y-m-d\TH:i'],'sort_order'=>['nullable','integer','min:0'],'is_active'=>['nullable','boolean'],'submit_action'=>['nullable',Rule::in(['save_and_create'])]],['name'=>'required|string|max:255','sub_title'=>'nullable|string|max:255','content'=>'nullable|string','seo_title'=>'nullable|string|max:255','seo_description'=>'nullable|string|max:500','seo_keywords'=>'nullable|string|max:255']); }
}
