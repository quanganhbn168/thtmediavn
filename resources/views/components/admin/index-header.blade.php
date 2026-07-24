@props(['description' => null, 'createUrl' => null, 'createLabel' => 'Thêm mới'])

<div class="admin-index-header">
    <div>
        @if($description)
            <p class="text-body-secondary mb-0">{{ $description }}</p>
        @endif
    </div>
    @if($createUrl)
        <a href="{{ $createUrl }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>{{ $createLabel }}
        </a>
    @endif
</div>
