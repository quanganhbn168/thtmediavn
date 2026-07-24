@php
    $children = $item->childrenRecursive ?? collect();
    $active = $item->isCurrent() || $item->hasCurrentDescendant();
@endphp
<li class="nav-item {{ $children->isNotEmpty() ? 'dropdown' : '' }}">
    <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif>
        @if($item->icon)<i class="{{ $item->icon }} me-1"></i>@endif
        {{ $item->getTranslation('title', 'vi') }}
        @if($children->isNotEmpty())<i class="bi bi-chevron-down ms-1 small"></i>@endif
    </a>
    @if($children->isNotEmpty())
        <ul class="dropdown-menu border-0 shadow-sm rounded-3 py-2">
            @foreach($children as $child)
                <li>
                    <a class="dropdown-item py-2 {{ $child->isCurrent() ? 'active' : '' }}" href="{{ $child->href }}" target="{{ $child->target ?: '_self' }}" @if($child->target === '_blank') rel="noopener" @endif>
                        @if($child->icon)<i class="{{ $child->icon }} me-1"></i>@endif
                        {{ $child->getTranslation('title', 'vi') }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</li>
