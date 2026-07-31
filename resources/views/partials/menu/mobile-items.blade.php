@foreach($items as $item)
    @php
        $children = $item->childrenRecursive ?? collect();
        $collapseId = $idPrefix.'-'.$item->id;
    @endphp
    @if($children->isNotEmpty())
        <button class="nav-link w-100 border-0 bg-transparent d-flex justify-content-between align-items-center text-start" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
            <span>@if($item->icon)<i class="{{ $item->icon }} me-1"></i>@endif{{ $item->getTranslation('title', 'vi') }}</span>
            <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse mobile-submenu" id="{{ $collapseId }}">
            <a class="fw-semibold" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif>Xem tất cả</a>
            @include('partials.menu.mobile-items', ['items' => $children, 'idPrefix' => $collapseId])
        </div>
    @else
        <a class="nav-link" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif>
            @if($item->icon)<i class="{{ $item->icon }} me-1"></i>@endif{{ $item->getTranslation('title', 'vi') }}
        </a>
    @endif
@endforeach
