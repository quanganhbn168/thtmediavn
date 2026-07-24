@props(['title' => 'Bộ lọc'])

<section {{ $attributes->class(['card', 'admin-filter-panel', 'mb-3']) }} aria-label="{{ $title }}">
    <div class="card-header border-0 pb-0">
        <h3 class="card-title"><i class="bi bi-funnel me-2"></i>{{ $title }}</h3>
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</section>
