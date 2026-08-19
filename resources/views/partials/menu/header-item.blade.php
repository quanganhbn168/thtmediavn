@php
    $children = $item->childrenRecursive ?? collect();
    $active = $item->isCurrent() || $item->hasCurrentDescendant();
    $itemTitle = trim((string) $item->getTranslation('title', 'vi'));
    $itemPath = parse_url($item->href, PHP_URL_PATH);
    $servicesPath = parse_url(route('services.index'), PHP_URL_PATH);
    $isServices = ($item->route ?? null) === 'services.index'
        || $itemPath === $servicesPath
        || str_contains(mb_strtolower($itemTitle), 'dịch vụ');
@endphp
<li class="site-navbar__item {{ $isServices ? 'site-navbar__item--mega' : ($children->isNotEmpty() ? 'site-navbar__item--dropdown' : '') }}">
    <a class="site-navbar__link {{ $active ? 'active' : '' }}" href="{{ $item->href }}" target="{{ $item->target ?: '_self' }}" @if($item->target === '_blank') rel="noopener" @endif @if($children->isNotEmpty()) aria-haspopup="true" @endif>
        @if($item->icon)<i class="{{ $item->icon }} mr-1"></i>@endif
        {{ $itemTitle }}
        @if($isServices || $children->isNotEmpty())<i class="fa-solid fa-chevron-down ml-1 text-xs" aria-hidden="true"></i>@endif
    </a>
    @if($isServices)
        @include('partials.menu.service-mega-menu')
    @elseif($children->isNotEmpty())
        <ul class="site-navbar__dropdown rounded-xl border border-line bg-white py-2 shadow-lg">
            @foreach($children as $child)
                @php
                    $grandchildren = $child->childrenRecursive ?? collect();
                    $childActive = $child->isCurrent() || $child->hasCurrentDescendant();
                @endphp
                <li class="{{ $grandchildren->isNotEmpty() ? 'site-navbar__submenu-item' : '' }}">
                    <a class="site-navbar__dropdown-link flex items-center justify-between gap-3 py-2 {{ $childActive ? 'active' : '' }}" href="{{ $child->href }}" target="{{ $child->target ?: '_self' }}" @if($child->target === '_blank') rel="noopener" @endif @if($grandchildren->isNotEmpty()) aria-haspopup="true" @endif>
                        @if($child->icon)<i class="{{ $child->icon }} mr-1"></i>@endif
                        {{ $child->getTranslation('title', 'vi') }}
                        @if($grandchildren->isNotEmpty())<i class="fa-solid fa-chevron-right text-xs"></i>@endif
                    </a>
                    @if($grandchildren->isNotEmpty())
                        <ul class="site-navbar__submenu rounded-xl border border-line bg-white py-2 shadow-lg">
                            @foreach($grandchildren as $grandchild)
                                <li>
                                    <a class="site-navbar__dropdown-link py-2 {{ $grandchild->isCurrent() ? 'active' : '' }}" href="{{ $grandchild->href }}" target="{{ $grandchild->target ?: '_self' }}" @if($grandchild->target === '_blank') rel="noopener" @endif>
                                        @if($grandchild->icon)<i class="{{ $grandchild->icon }} mr-1"></i>@endif
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
