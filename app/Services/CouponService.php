<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Coupon::query();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function editorContext(Coupon $coupon): array
    {
        return [
            'coupon' => $coupon,
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id'),
            'categories' => ProductCategory::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ];
    }

    public function create(array $data): Coupon
    {
        $coupon = Coupon::create($this->toPayload($data));
        $this->sync($coupon, $data);

        return $coupon;
    }

    public function update(Coupon $coupon, array $data): void
    {
        $coupon->update($this->toPayload($data));
        $this->sync($coupon, $data);
    }

    public function delete(Coupon $coupon): void
    {
        $coupon->delete();
    }

    private function toPayload(array $data): array
    {
        if (($data['type'] ?? null) === 'free_shipping') {
            $data['value'] = 0;
        }

        return [
            'code' => mb_strtoupper((string) $data['code']),
            'name' => $data['name'],
            'type' => $data['type'],
            'value' => $data['value'] ?? 0,
            'max_discount' => $data['max_discount'] ?? null,
            'minimum_order' => $data['minimum_order'] ?? 0,
            'usage_limit' => $data['usage_limit'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,
            'starts_at' => $data['starts_at'] ?: null,
            'ends_at' => $data['ends_at'] ?: null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function sync(Coupon $coupon, array $data): void
    {
        $coupon->products()->sync($data['product_ids'] ?? []);
        $coupon->categories()->sync($data['category_ids'] ?? []);
    }
}
