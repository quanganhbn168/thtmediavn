@foreach($items as $item)
    @php
        $children = $item->childrenRecursive ?? collect();
        $collapseId = $idPrefix.'-'.$item->id;
    @endphp
    @if($children->isNotEmpty())
        <button class="mobile-menu-list__link mobile-menu-toggle flex items-center justify-between border-0 bg-transparent text-left" type="button" data-mobile-collapse-toggle="{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
            <span>@if($item->icon)<i class="{{ $item->icon }} mr-1"></i>@endif{{ $item->getTranslation('title', 'vi') }}</span>
            <i class="fa-solid fa-chevron-down"></i>
        </button>
        <div class="mobile-submenu" id="{{ $collapseId }}">
            <a class="font-semibold" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif>Xem tất cả</a>
            @include('partials.menu.mobile-items', ['items' => $children, 'idPrefix' => $collapseId])
        </div>
    @else
        <a class="mobile-menu-list__link" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif>
            @if($item->icon)<i class="{{ $item->icon }} mr-1"></i>@endif{{ $item->getTranslation('title', 'vi') }}
        </a>
    @endif
@endforeach
