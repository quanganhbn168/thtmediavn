<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CategoryHierarchyService
{
    public const MAX_DEPTH = 4;

    /**
     * Build a displayable tree from a flat category collection.
     *
     * Each row carries the full path, current depth and whether it can be
     * selected in a leaf-only or parent-selection context.
     *
     * @param  iterable<Model>  $categories
     * @param  array<int>  $excludeIds
     * @return array<int, array{id: int, label: string, path: string, depth: int, disabled: bool}>
     */
    public function selectOptions(
        iterable $categories,
        bool $leafOnly = false,
        bool $activeOnly = false,
        bool $parentMode = false,
        array $excludeIds = [],
    ): array {
        $items = collect($categories)
            ->filter(fn ($category) => $category instanceof Model && $category->getKey())
            ->keyBy(fn (Model $category) => (int) $category->getKey());

        $children = $items->groupBy(fn (Model $category) => (int) ($category->getAttribute('parent_id') ?? 0));
        $excluded = array_fill_keys(array_map('intval', $excludeIds), true);
        $options = [];

        $walk = function (int $parentId, int $depth, array $path, bool $ancestorsAreActive, array $trail) use (&$walk, $children, $excluded, $leafOnly, $activeOnly, $parentMode, &$options): void {
            foreach ($children->get($parentId, collect()) as $category) {
                $id = (int) $category->getKey();
                if (isset($trail[$id])) {
                    continue;
                }

                $name = $this->nameOf($category);
                $currentPath = [...$path, $name];
                $isActive = (bool) $category->getAttribute('is_active');
                $activePath = $ancestorsAreActive && $isActive;
                $hasChildren = $children->has($id) && $children->get($id)->isNotEmpty();
                $isSelectable = ! isset($excluded[$id]);
                if ($leafOnly) {
                    $isSelectable = $isSelectable && ! $hasChildren && (! $activeOnly || $activePath);
                } elseif ($parentMode) {
                    $isSelectable = $isSelectable && $depth < self::MAX_DEPTH;
                } elseif ($activeOnly) {
                    $isSelectable = $isSelectable && $activePath;
                }

                $options[] = [
                    'id' => $id,
                    'label' => str_repeat('— ', max(0, $depth - 1)).$name,
                    'path' => implode(' › ', $currentPath),
                    'depth' => $depth,
                    'disabled' => ! $isSelectable,
                ];

                $walk($id, $depth + 1, $currentPath, $activePath, [...$trail, $id => true]);
            }
        };

        $walk(0, 1, [], true, []);

        return $options;
    }

    /**
     * Return an id and all of its descendants, suitable for excluding invalid
     * parent choices while editing a category.
     *
     * @param  iterable<Model>  $categories
     * @return array<int>
     */
    public function descendantIds(iterable $categories, ?int $categoryId): array
    {
        if (! $categoryId) {
            return [];
        }

        $children = collect($categories)
            ->filter(fn ($category) => $category instanceof Model && $category->getKey())
            ->groupBy(fn (Model $category) => (int) ($category->getAttribute('parent_id') ?? 0));
        $ids = [];

        $walk = function (int $id, array $trail) use (&$walk, $children, &$ids): void {
            if (isset($trail[$id])) {
                return;
            }

            $ids[] = $id;
            foreach ($children->get($id, collect()) as $child) {
                $walk((int) $child->getKey(), [...$trail, $id => true]);
            }
        };

        $walk($categoryId, []);

        return array_values(array_unique($ids));
    }

    /**
     * Confirm that a category can receive a child without breaking the four
     * level hierarchy.
     */
    public function parentAssignmentError(
        string $modelClass,
        ?int $parentId,
        ?int $categoryId,
        string $contentRelation,
        string $contentLabel,
    ): ?string {
        $categories = $modelClass::query()->get(['id', 'parent_id']);
        $byId = $categories->keyBy(fn (Model $category) => (int) $category->getKey());

        if ($parentId === null) {
            if ($categoryId && $byId->has($categoryId) && $this->subtreeHeight($categories, $categoryId) > self::MAX_DEPTH) {
                return 'Cây danh mục vượt quá giới hạn 4 cấp.';
            }

            return null;
        }

        if (! $byId->has($parentId)) {
            return 'Danh mục cha không tồn tại.';
        }

        if ($categoryId && in_array($parentId, $this->descendantIds($categories, $categoryId), true)) {
            return 'Không thể chọn chính danh mục này hoặc danh mục con của nó làm danh mục cha.';
        }

        $parentDepth = $this->depthOf($byId, $parentId);
        if ($parentDepth === null) {
            return 'Cấu trúc danh mục cha không hợp lệ.';
        }

        $subtreeHeight = $categoryId && $byId->has($categoryId)
            ? $this->subtreeHeight($categories, $categoryId)
            : 1;
        if ($parentDepth + $subtreeHeight > self::MAX_DEPTH) {
            return 'Danh mục chỉ được phép sâu tối đa 4 cấp.';
        }

        return null;
    }

    /**
     * Confirm that a category is an active leaf whose full parent path is
     * active and within the permitted hierarchy depth.
     */
    public function leafAssignmentError(Model $category, string $label): ?string
    {
        $modelClass = $category::class;
        if ($modelClass::query()->where('parent_id', $category->getKey())->exists()) {
            return "{$label} phải là danh mục lá, không được có danh mục con.";
        }

        $depth = 0;
        $current = $category;
        $seen = [];
        while ($current) {
            $id = (int) $current->getKey();
            if (isset($seen[$id])) {
                return 'Cấu trúc danh mục đang có vòng lặp.';
            }
            $seen[$id] = true;

            $depth++;
            if ($depth > self::MAX_DEPTH) {
                return 'Danh mục được chọn vượt quá giới hạn 4 cấp.';
            }

            if (! (bool) $current->getAttribute('is_active')) {
                return "{$label} và toàn bộ danh mục cha phải đang hoạt động.";
            }

            $parentId = $current->getAttribute('parent_id');
            $current = $parentId ? $modelClass::query()->find($parentId) : null;
            if ($parentId && ! $current) {
                return 'Cấu trúc danh mục cha không hợp lệ.';
            }
        }

        return null;
    }

    private function nameOf(Model $category): string
    {
        if (method_exists($category, 'getTranslation')) {
            return (string) $category->getTranslation('name', app()->getLocale());
        }

        return (string) $category->getAttribute('name');
    }

    /** @param Collection<int, Model> $categories */
    private function depthOf(Collection $categories, int $categoryId): ?int
    {
        $depth = 0;
        $currentId = $categoryId;
        $seen = [];

        while ($currentId) {
            if (isset($seen[$currentId]) || ! $categories->has($currentId)) {
                return null;
            }

            $seen[$currentId] = true;
            $depth++;
            $currentId = (int) ($categories->get($currentId)->getAttribute('parent_id') ?? 0);
        }

        return $depth;
    }

    /** @param Collection<int, Model> $categories */
    private function subtreeHeight(Collection $categories, int $categoryId): int
    {
        $children = $categories->groupBy(fn (Model $category) => (int) ($category->getAttribute('parent_id') ?? 0));
        $height = function (int $id, array $trail) use (&$height, $children): int {
            if (isset($trail[$id])) {
                return self::MAX_DEPTH + 1;
            }

            $childHeights = $children->get($id, collect())
                ->map(fn (Model $child) => $height((int) $child->getKey(), [...$trail, $id => true]));

            return $childHeights->isEmpty() ? 1 : 1 + $childHeights->max();
        };

        return $height($categoryId, []);
    }
}
