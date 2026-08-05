<?php

namespace App\Services;

use App\Models\Combo;
use App\Models\ComboItem;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ComboComponentService
{
    public function paginate(Combo $combo, array $filters): LengthAwarePaginator
    {
        $query = $combo->items()->with(['product', 'variant']);
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->whereHas('product', fn ($builder) => $builder->where('name', 'like', "%{$search}%"));
        }

        return $query->orderBy('sort_order')->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function editorContext(Combo $combo, ComboItem $component): array
    {
        return [
            'combo' => $combo,
            'comboItem' => $component->loadMissing(['product', 'variant']),
            'componentProducts' => Product::query()
                ->where('is_active', true)
                ->where('status', 'active')
                ->with(['activeVariants' => fn ($query) => $query->orderByDesc('is_default')->orderBy('id')])
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    public function create(Combo $combo, array $data): ComboItem
    {
        return $combo->items()->create($this->payload($data));
    }

    public function update(ComboItem $component, array $data): void
    {
        $component->update($this->payload($data));
    }

    public function delete(ComboItem $component): void
    {
        $component->delete();
    }

    private function payload(array $data): array
    {
        return [
            'product_id' => (int) $data['product_id'],
            'product_variant_id' => filled($data['product_variant_id'] ?? null) ? (int) $data['product_variant_id'] : null,
            'quantity' => (int) $data['quantity'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
