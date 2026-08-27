@foreach ($faviconLinks as $faviconLink)
    <link rel="{{ $faviconLink['rel'] }}" type="{{ $faviconLink['type'] }}" @isset($faviconLink['sizes']) sizes="{{ $faviconLink['sizes'] }}" @endisset @isset($faviconLink['color']) color="{{ $faviconLink['color'] }}" @endisset href="{{ $faviconLink['href'] }}" />
@endforeach
