@php
    $children = $item->childrenRecursive ?? collect();
    $active = $item->isCurrent() || $item->hasCurrentDescendant();
@endphp
<li class="nav-item {{ $children->isNotEmpty() ? 'dropdown' : '' }}">
    <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif @if($children->isNotEmpty()) aria-haspopup="true" @endif>
        @if($item->icon)<i class="{{ $item->icon }} me-1"></i>@endif
        {{ $item->getTranslation('title', 'vi') }}
        @if($children->isNotEmpty())<i class="bi bi-chevron-down ms-1 small"></i>@endif
    </a>
    @if($children->isNotEmpty())
        <ul class="dropdown-menu border-0 shadow-sm rounded-3 py-2">
            @foreach($children as $child)
                @php
                    $grandchildren = $child->childrenRecursive ?? collect();
                    $childActive = $child->isCurrent() || $child->hasCurrentDescendant();
                @endphp
                <li class="{{ $grandchildren->isNotEmpty() ? 'dropdown-submenu' : '' }}">
                    <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-3 {{ $childActive ? 'active' : '' }}" href="{{ $child->href }}" target="{{ $child->target ?: '_self' }}" @if($child->target === '_blank') rel="noopener" @endif @if($grandchildren->isNotEmpty()) aria-haspopup="true" @endif>
                        @if($child->icon)<i class="{{ $child->icon }} me-1"></i>@endif
                        {{ $child->getTranslation('title', 'vi') }}
                        @if($grandchildren->isNotEmpty())<i class="bi bi-chevron-right small"></i>@endif
                    </a>
                    @if($grandchildren->isNotEmpty())
                        <ul class="dropdown-menu dropdown-submenu-menu border-0 shadow-sm rounded-3 py-2">
                            @foreach($grandchildren as $grandchild)
                                <li>
                                    <a class="dropdown-item py-2 {{ $grandchild->isCurrent() ? 'active' : '' }}" href="{{ $grandchild->href }}" target="{{ $grandchild->target ?: '_self' }}" @if($grandchild->target === '_blank') rel="noopener" @endif>
                                        @if($grandchild->icon)<i class="{{ $grandchild->icon }} me-1"></i>@endif
                                        {{ $grandchild->getTranslation('title', 'vi') }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</li>
