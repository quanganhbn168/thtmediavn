@props([
    'title',
    'href' => null,
    'linkText' => 'Xem tất cả',
    'center' => true,
])
<div class="section-heading {{ $center ? 'text-center' : '' }} {{ !$center ? 'flex flex-col items-start justify-between gap-3 md:flex-row md:items-end' : '' }}">
    <div>
        <h2 class="section-heading-title">{{ $title }}</h2>
    </div>
    @if($href)
        <a class="section-link shrink-0" href="{{ $href }}">{{ $linkText }} <i class="fa-solid fa-arrow-right ml-1"></i></a>
    @endif
</div>
