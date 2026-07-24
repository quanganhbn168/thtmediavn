<?php
namespace App\Http\Requests\Admin\Page;
class UpdatePageRequest extends StorePageRequest { public function rules(): array { $rules=parent::rules();unset($rules['submit_action']);return $rules; } }
