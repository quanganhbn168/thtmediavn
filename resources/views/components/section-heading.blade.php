@props([
    'title',
    'href' => null,
    'linkText' => 'Xem tất cả',
    'center' => true,
])
<div class="section-heading {{ $center ? 'text-center' : '' }} {{ !$center ? 'd-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3' : '' }}">
    <div>
        <h2 class="section-heading-title">{{ $title }}</h2>
    </div>
    @if($href)
        <a class="section-link flex-shrink-0" href="{{ $href }}">{{ $linkText }} <i class="bi bi-arrow-right ms-1"></i></a>
    @endif
</div>
