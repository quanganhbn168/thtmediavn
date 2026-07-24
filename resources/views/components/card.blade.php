@props([
    'type' => 'primary',
    'outline' => true,
    'title' => null,
    'bodyClass' => '',
    'collapsible' => false,
    'maximizable' => false,
    'removable' => false,
])

<div {{ $attributes->class(['card', 'card-outline' => $outline, 'card-'.$type]) }}>
    @if($title || isset($tools) || isset($header) || $collapsible || $maximizable || $removable)
        <div class="card-header">
            @if($title)
                <h3 class="card-title mb-0">{{ $title }}</h3>
            @endif
            
            <div class="card-tools">
                @if(isset($tools))
                    {{ $tools }}
                @elseif(isset($header))
                    {{ $header }}
                @endif

                @if($maximizable)
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-maximize" title="Phóng to">
                        <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                        <i data-lte-icon="minimize" class="bi bi-fullscreen-exit"></i>
                    </button>
                @endif
                @if($collapsible)
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" title="Thu gọn">
                        <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                        <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                    </button>
                @endif
                @if($removable)
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-remove" title="Đóng">
                        <i class="bi bi-x-lg"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
