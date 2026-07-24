@foreach($items as $item)
    <a class="footer-link" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif>
        @if($item->icon)<i class="{{ $item->icon }} me-1"></i>@endif{{ $item->getTranslation('title', 'vi') }}
    </a>
    @foreach($item->childrenRecursive ?? [] as $child)
        <a class="footer-link footer-sublink" href="{{ $child->href }}" target="{{ $child->target ?: '_self' }}" @if($child->target === '_blank') rel="noopener" @endif>{{ $child->getTranslation('title', 'vi') }}</a>
    @endforeach
@endforeach
