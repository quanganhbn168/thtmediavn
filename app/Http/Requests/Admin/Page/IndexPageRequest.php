<?php
namespace App\Http\Requests\Admin\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class IndexPageRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return ['search'=>['nullable','string','max:100'],'template'=>['nullable','string','max:100'],'status'=>['nullable',Rule::in(['active','inactive'])],'sort'=>['nullable',Rule::in(['manual','name','published_at','created_at'])],'direction'=>['nullable',Rule::in(['asc','desc'])],'per_page'=>['nullable','integer',Rule::in([10,25,50])]]; }
}
