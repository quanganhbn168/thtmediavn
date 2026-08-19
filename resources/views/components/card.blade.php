@props([
    'type' => 'primary',
    'outline' => true,
    'title' => null,
    'bodyClass' => '',
    'collapsible' => false,
    'maximizable' => false,
    'removable' => false,
])

<div {{ $attributes->class(['overflow-hidden rounded-2xl border border-line bg-white shadow-sm', 'ring-1 ring-primary/10' => $outline]) }}>
    @if($title || isset($tools) || isset($header) || $collapsible || $maximizable || $removable)
        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
            @if($title)
                <h3 class="m-0 text-lg font-bold text-ink">{{ $title }}</h3>
            @endif

            <div class="flex items-center gap-1">
                @if(isset($tools))
                    {{ $tools }}
                @elseif(isset($header))
                    {{ $header }}
                @endif

                @if($maximizable)
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm text-primary transition duration-200 hover:-translate-y-px hover:bg-primary-soft hover:text-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" data-lte-toggle="card-maximize" title="Phóng to">
                        <i data-lte-icon="maximize" class="fa-solid fa-expand"></i>
                        <i data-lte-icon="minimize" class="fa-solid fa-compress"></i>
                    </button>
                @endif
                @if($collapsible)
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm text-primary transition duration-200 hover:-translate-y-px hover:bg-primary-soft hover:text-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" data-lte-toggle="card-collapse" title="Thu gọn">
                        <i data-lte-icon="expand" class="fa-solid fa-plus"></i>
                        <i data-lte-icon="collapse" class="fa-solid fa-minus"></i>
                    </button>
                @endif
                @if($removable)
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm text-primary transition duration-200 hover:-translate-y-px hover:bg-red-50 hover:text-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700" data-lte-toggle="card-remove" title="Đóng">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif
    <div class="p-5 {{ $bodyClass }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="border-t border-line px-5 py-4">
            {{ $footer }}
        </div>
    @endif
</div>
