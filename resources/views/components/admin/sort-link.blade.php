@props(['label', 'column'])
@php
    $active = request('sort', 'manual') === $column;
    $direction = $active && request('direction', 'asc') === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction, 'page' => null]);
    $icon = ! $active ? 'bi-chevron-expand' : (request('direction', 'asc') === 'asc' ? 'bi-sort-up' : 'bi-sort-down');
@endphp
<a class="admin-sort-link" href="{{ $url }}">
    {{ $label }} <i class="bi {{ $icon }}"></i>
</a>
