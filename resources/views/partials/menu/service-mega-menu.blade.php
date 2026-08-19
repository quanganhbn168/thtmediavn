@php
    $serviceCategories = ($serviceCategories ?? collect())
        ->filter(fn ($category): bool => filled($category->getSlug('vi')))
        ->values();
@endphp

<div class="site-service-mega" role="region" aria-label="Danh mục dịch vụ THT Media">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="site-service-mega__grid">
            @forelse($serviceCategories as $category)
                <section class="site-service-mega__group">
                    <h3><a href="{{ route('services.show', $category->getSlug('vi')) }}">{{ $category->getTranslation('name', 'vi') }}</a></h3>
                    <ul>
                        @forelse($category->services as $service)
                            <li><a href="{{ route('services.show', $service->getSlug('vi')) }}"><strong>{{ $service->getTranslation('name', 'vi') }}</strong></a></li>
                        @empty
                            <li><a href="{{ route('services.show', $category->getSlug('vi')) }}">Xem các dịch vụ trong danh mục</a></li>
                        @endforelse
                    </ul>
                </section>
            @empty
                <section class="site-service-mega__group">
                    <h3><a href="{{ route('services.index') }}">Dịch vụ</a></h3>
                    <ul><li><a href="{{ route('services.index') }}">Xem toàn bộ dịch vụ</a></li></ul>
                </section>
            @endforelse
        </div>
    </div>
</div>
