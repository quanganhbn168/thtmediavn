@props([
    'categories' => [],
    'name',
    'label' => 'Danh mục',
    'selected' => null,
    'placeholder' => '--- Chọn danh mục ---',
    'required' => false,
    'multiple' => false,
    'leafOnly' => false,
    'activeOnly' => false,
    'parentMode' => false,
    'excludeIds' => [],
    'id' => null,
])

@php
    $selectId = $id ?? 'category_tree_'.str_replace(['[', ']'], ['_', ''], $name);
    $selectedValues = collect(is_array($selected) ? $selected : [$selected])
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->map(fn ($value) => (string) $value)
        ->all();
    $selectName = $name.($multiple && ! \Illuminate\Support\Str::endsWith($name, '[]') ? '[]' : '');
    $options = app(\App\Services\CategoryHierarchyService::class)->selectOptions(
        $categories,
        leafOnly: (bool) $leafOnly,
        activeOnly: (bool) $activeOnly,
        parentMode: (bool) $parentMode,
        excludeIds: $excludeIds,
    );
@endphp

<div class="mb-3" data-category-tree-select>
    @if($label)
        <label for="{{ $selectId }}" class="form-label font-weight-bold">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <select
        name="{{ $selectName }}"
        id="{{ $selectId }}"
        class="form-select @error($name) is-invalid @enderror"
        @if($multiple) multiple @endif
        @if($required) required @endif
        data-tom-select="1"
        data-tom-select-create="0"
        data-tom-select-sort="tree"
        data-placeholder="{{ $placeholder }}"
        {{ $attributes }}
    >
        @if(! $multiple && $placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $option)
            <option
                value="{{ $option['id'] }}"
                @selected(in_array((string) $option['id'], $selectedValues, true))
                @disabled($option['disabled'])
                data-path="{{ $option['path'] }}"
            >{{ $option['label'] }}
            </option>
        @endforeach
    </select>

    @if($leafOnly)
        <div class="form-text">Chỉ chọn được danh mục lá đang hoạt động, sâu tối đa 4 cấp.</div>
    @elseif($parentMode)
        <div class="form-text">Chọn danh mục cha để tạo cây tối đa 4 cấp.</div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
