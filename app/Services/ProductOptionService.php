<?php

namespace App\Services;

use App\Models\ProductOption;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductOptionService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = ProductOption::query()->with('values');
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

    public function formContext(ProductOption $option): array
    {
        return ['option' => $option->loadMissing('values')];
    }

    public function create(array $data): ProductOption
    {
        return DB::transaction(function () use ($data): ProductOption {
            $option = ProductOption::create($this->payload($data));
            $this->syncValues($option, $data);

            return $option;
        });
    }

    public function update(ProductOption $option, array $data): void
    {
        DB::transaction(function () use ($option, $data): void {
            $option->update($this->payload($data));
            $this->syncValues($option, $data);
        });
    }

    public function delete(ProductOption $option): void
    {
        $option->delete();
    }

    private function payload(array $data): array
    {
        return [
            'name' => $data['name'],
            'slug' => trim((string) ($data['slug'] ?? '')) !== '' ? trim((string) $data['slug']) : Str::slug($data['name']),
            'display_type' => $data['display_type'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function syncValues(ProductOption $option, array $data): void
    {
        $ids = [];
        $slugs = [];
        foreach ($data['values'] ?? [] as $index => $value) {
            if (! is_array($value) || ! isset($value['value']) || trim((string) $value['value']) === '') {
                continue;
            }

            $valueName = trim((string) $value['value']);
            $slug = Str::slug($valueName);
            if ($slug === '') {
                throw ValidationException::withMessages([
                    "values.{$index}.value" => 'Giá trị thuộc tính không hợp lệ.',
                ]);
            }

            if (isset($slugs[$slug])) {
                throw ValidationException::withMessages([
                    "values.{$index}.value" => 'Giá trị thuộc tính không được trùng nhau.',
                ]);
            }
            $slugs[$slug] = true;

            $valueId = $value['id'] ?? null;
            if ($valueId !== null && ! $option->values()->whereKey($valueId)->exists()) {
                throw ValidationException::withMessages([
                    "values.{$index}.value" => 'Giá trị thuộc tính không hợp lệ.',
                ]);
            }

            $model = $option->values()->updateOrCreate(
                ['id' => $valueId],
                [
                    'value' => $valueName,
                    'slug' => $slug,
                    'color_code' => $value['color_code'] ?? null,
                    'sort_order' => $index,
                ]
            );

            $ids[] = $model->id;
        }

        $query = $option->values();
        if ($ids !== []) {
            $query->whereNotIn('id', $ids);
        }
        $query->whereDoesntHave('variants')->delete();
    }
}
