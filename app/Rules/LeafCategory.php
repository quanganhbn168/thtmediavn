<?php

namespace App\Rules;

use App\Services\CategoryHierarchyService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class LeafCategory implements ValidationRule
{
    /** @param class-string<Model> $modelClass */
    public function __construct(
        private readonly string $modelClass,
        private readonly string $label,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $category = $this->modelClass::query()->find($value);
        if (! $category) {
            return;
        }

        $error = app(CategoryHierarchyService::class)->leafAssignmentError($category, $this->label);
        if ($error) {
            $fail($error);
        }
    }
}
