<?php

namespace App\Services;

use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductCategoryService
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly CategoryHierarchyService $categoryHierarchy,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = ProductCategory::query()->with('parent');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (filled($filters['parent_id'] ?? null)) {
            $query->where('parent_id', (int) $filters['parent_id']);
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        if (($filters['featured'] ?? null) === 'yes') {
            $query->where('is_featured', true);
        } elseif (($filters['featured'] ?? null) === 'no') {
            $query->where('is_featured', false);
        }

        if (($filters['home'] ?? null) === 'yes') {
            $query->where('is_home', true);
        } elseif (($filters['home'] ?? null) === 'no') {
            $query->where('is_home', false);
        }

        return $query->orderBy('sort_order')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function filterCategories(): Collection
    {
        return ProductCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name']);
    }

    public function formContext(ProductCategory $category): array
    {
        $categories = ProductCategory::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return [
            'category' => $category,
            'categories' => $categories,
            'excludedParentIds' => $this->categoryHierarchy->descendantIds($categories, $category->exists ? $category->id : null),
        ];
    }

    public function create(array $data): ProductCategory
    {
        $payload = $this->payload($data);
        $payload['image'] = $this->persistImage($data['image'] ?? null);

        if (! array_key_exists('sort_order', $data) || blank($data['sort_order'])) {
            $payload['sort_order'] = $this->nextSortOrder($payload['parent_id']);
        }

        return ProductCategory::create($payload);
    }

    public function update(ProductCategory $category, array $data): void
    {
        $payload = $this->payload($data, $category);
        $newImage = $this->normalizeImagePath($data['image'] ?? null);
        $removeImage = (bool) ($data['image_remove'] ?? false);

        if ($removeImage) {
            $this->deleteManagedImage($category->image);
            $payload['image'] = null;
        } elseif ($newImage !== null) {
            $permanentImage = $this->persistImage($newImage);
            if ($permanentImage !== $category->image) {
                $this->deleteManagedImage($category->image);
            }
            $payload['image'] = $permanentImage;
        } else {
            $payload['image'] = $category->image;
        }

        $category->update($payload);
    }

    public function delete(ProductCategory $category): void
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            throw ValidationException::withMessages([
                'ids' => 'Không thể xóa danh mục đang có sản phẩm hoặc danh mục con.',
            ]);
        }

        $this->deleteManagedImage($category->image);
        $category->delete();
    }

    private function payload(array $data, ?ProductCategory $current = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $seoTitle = trim((string) ($data['seo_title'] ?? ''));
        $seoDescription = trim((string) ($data['seo_description'] ?? ''));

        return [
            'parent_id' => $this->toNullableInt($data['parent_id'] ?? null),
            'name' => $name,
            'slug' => $slug !== '' ? $slug : ($current?->slug ?: Str::slug($name)),
            'description' => $description !== '' ? $description : null,
            'seo_title' => $seoTitle !== '' ? $seoTitle : ($current?->seo_title ?: $name),
            'seo_description' => $seoDescription !== '' ? $seoDescription : ($current?->seo_description ?: ($description !== '' ? $description : null)),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_home' => (bool) ($data['is_home'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    private function nextSortOrder(?int $parentId): int
    {
        $query = ProductCategory::query();

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        return ((int) $query->max('sort_order')) + 1;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function persistImage(mixed $path): ?string
    {
        $path = $this->normalizeImagePath($path);
        if ($path === null || ! Str::startsWith($path, 'uploads/tmp/')) {
            return $path;
        }

        if (basename($path) !== substr($path, strlen('uploads/tmp/'))) {
            throw ValidationException::withMessages(['image' => 'Đường dẫn ảnh tạm không hợp lệ.']);
        }

        $temporaryRoot = realpath(public_path('uploads/tmp'));
        $temporaryFile = realpath(public_path($path));
        if ($temporaryRoot === false || $temporaryFile === false || ! File::isFile($temporaryFile)) {
            throw ValidationException::withMessages(['image' => 'Ảnh tạm không tồn tại hoặc đã hết hạn.']);
        }

        $root = rtrim(str_replace('\\', '/', $temporaryRoot), '/').'/';
        $candidate = str_replace('\\', '/', $temporaryFile);
        if (! Str::startsWith($candidate, $root)) {
            throw ValidationException::withMessages(['image' => 'Đường dẫn ảnh tạm không hợp lệ.']);
        }

        return $this->imageService->moveToPermanent($path, 'product-categories');
    }

    private function deleteManagedImage(?string $path): void
    {
        $path = $this->normalizeImagePath($path);
        if ($path === null || ! Str::startsWith($path, 'uploads/product-categories/')) {
            return;
        }

        if (basename($path) !== substr($path, strlen('uploads/product-categories/'))) {
            return;
        }

        File::delete(public_path($path));
    }

    private function normalizeImagePath(mixed $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path));

        return $path !== '' ? $path : null;
    }
}
