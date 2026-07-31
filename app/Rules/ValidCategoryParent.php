<?php

namespace App\Rules;

use App\Services\CategoryHierarchyService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class ValidCategoryParent implements ValidationRule
{
    /** @param class-string<Model> $modelClass */
    public function __construct(
        private readonly string $modelClass,
        private readonly ?int $categoryId,
        private readonly string $contentRelation,
        private readonly string $contentLabel,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parentId = $value === null || $value === '' ? null : (int) $value;
        $error = app(CategoryHierarchyService::class)->parentAssignmentError(
            $this->modelClass,
            $parentId,
            $this->categoryId,
            $this->contentRelation,
            $this->contentLabel,
        );

        if ($error) {
            $fail($error);
        }
    }
}
