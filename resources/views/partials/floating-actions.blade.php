<div class="floating-actions" aria-label="Liên hệ nhanh">
    @foreach($contactChannels->where('show_floating', true) as $channel)
        <a class="floating-action {{ $channel->type }}" href="{{ $channel->link }}" @if(in_array($channel->type, ['zalo', 'messenger', 'other'], true)) target="_blank" rel="noopener" @endif aria-label="{{ $channel->name }}">
            @if($channel->type === 'zalo')
                <img class="zalo-icon" src="{{ asset('assets/images/zalo.svg') }}" alt="">
            @else
                <i class="bi bi-{{ $channel->type === 'phone' ? 'telephone-fill' : 'chat-fill' }}"></i>
            @endif
            <span class="floating-tooltip">{{ $channel->name }}</span>
        </a>
    @endforeach
    <button class="floating-action back-to-top" type="button" data-back-to-top aria-label="Lên đầu trang">
        <i class="bi bi-chevron-up"></i>
    </button>
</div>
