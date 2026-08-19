@foreach($items as $item)
    <a class="footer-link" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif>
        @if($item->icon)<i class="{{ $item->icon }} mr-1"></i>@endif{{ $item->getTranslation('title', 'vi') }}
    </a>
    @foreach($item->childrenRecursive ?? [] as $child)
        <a class="footer-link footer-sublink" href="{{ $child->href }}" target="{{ $child->target ?: '_self' }}" @if($child->target === '_blank') rel="noopener" @endif>{{ $child->getTranslation('title', 'vi') }}</a>
        @foreach($child->childrenRecursive ?? [] as $grandchild)
            <a class="footer-link footer-sublink ps-4" href="{{ $grandchild->href }}" target="{{ $grandchild->target ?: '_self' }}" @if($grandchild->target === '_blank') rel="noopener" @endif>{{ $grandchild->getTranslation('title', 'vi') }}</a>
        @endforeach
    @endforeach
@endforeach
