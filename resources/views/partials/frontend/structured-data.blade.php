@php
    $schemaValue = $schema ?? null;
    $schemaItems = is_array($schemaValue) && array_is_list($schemaValue)
        ? $schemaValue
        : (filled($schemaValue) ? [$schemaValue] : []);
@endphp
@foreach($schemaItems as $schemaItem)
    @if(is_array($schemaItem) && filled($schemaItem))
        @push('structured_data')
            <script type="application/ld+json">{!! json_encode($schemaItem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endpush
    @endif
@endforeach
