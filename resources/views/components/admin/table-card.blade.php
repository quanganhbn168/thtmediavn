@props(['title', 'total' => null])

<section {{ $attributes->class(['card', 'card-outline', 'card-primary', 'admin-table-card']) }}>
    <div class="card-header">
        <h3 class="card-title mb-0">{{ $title }}</h3>
        <div class="card-tools">
            @isset($tools){{ $tools }}@endisset
        </div>
    </div>
    {{ $slot }}
    @isset($footer)
        @if($footer->hasActualContent())
            <div class="card-footer clearfix">{{ $footer }}</div>
        @endif
    @endisset
</section>
