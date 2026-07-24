<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BrandService
{
    public function __construct(private readonly ImageService $imageService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Brand::query()->withCount('products');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('sort_order')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function formContext(Brand $brand): array
    {
        if ($brand->exists) {
            $brand->loadCount('products');
        }

        return ['brand' => $brand];
    }

    public function create(array $data): Brand
    {
        $payload = $this->payload($data);
        $payload['logo'] = $this->persistLogo($data['logo'] ?? null);

        return Brand::create($payload);
    }

    public function update(Brand $brand, array $data): void
    {
        $payload = $this->payload($data);
        $newLogo = $this->normalizeLogoPath($data['logo'] ?? null);
        $removeLogo = (bool) ($data['logo_remove'] ?? false);

        if ($removeLogo) {
            $this->deleteManagedLogo($brand->logo);
            $payload['logo'] = null;
        } elseif ($newLogo !== null) {
            $permanentLogo = $this->persistLogo($newLogo);
            if ($permanentLogo !== $brand->logo) {
                $this->deleteManagedLogo($brand->logo);
            }
            $payload['logo'] = $permanentLogo;
        } else {
            $payload['logo'] = $brand->logo;
        }

        $brand->update($payload);
    }

    public function delete(Brand $brand): void
    {
        $brand->delete();
    }

    private function payload(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));

        return [
            'name' => $name,
            'slug' => $slug !== '' ? $slug : Str::slug($name),
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    private function persistLogo(mixed $path): ?string
    {
        $path = $this->normalizeLogoPath($path);
        if ($path === null || ! Str::startsWith($path, 'uploads/tmp/')) {
            return $path;
        }

        if (basename($path) !== substr($path, strlen('uploads/tmp/'))) {
            throw ValidationException::withMessages(['logo' => 'Đường dẫn logo tạm không hợp lệ.']);
        }

        $temporaryRoot = realpath(public_path('uploads/tmp'));
        $temporaryFile = realpath(public_path($path));
        if ($temporaryRoot === false || $temporaryFile === false || ! File::isFile($temporaryFile)) {
            throw ValidationException::withMessages(['logo' => 'Logo tạm không tồn tại hoặc đã hết hạn.']);
        }

        $root = rtrim(str_replace('\\', '/', $temporaryRoot), '/').'/';
        $candidate = str_replace('\\', '/', $temporaryFile);
        if (! Str::startsWith($candidate, $root)) {
            throw ValidationException::withMessages(['logo' => 'Đường dẫn logo tạm không hợp lệ.']);
        }

        return $this->imageService->moveToPermanent($path, 'brands');
    }

    private function deleteManagedLogo(?string $path): void
    {
        $path = $this->normalizeLogoPath($path);
        if ($path === null || ! Str::startsWith($path, 'uploads/brands/')) {
            return;
        }

        if (basename($path) !== substr($path, strlen('uploads/brands/'))) {
            return;
        }

        File::delete(public_path($path));
    }

    private function normalizeLogoPath(mixed $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path));

        return $path !== '' ? $path : null;
    }
}
