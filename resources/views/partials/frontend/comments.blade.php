@php
    $commentableType = $commentableType ?? 'post';
    $commentableId = $commentable->getKey();
    $comments = $comments ?? collect();
@endphp

<section class="comments-panel" id="binh-luan" aria-labelledby="comments-title">
    <div class="comments-panel__heading">
        <div>
            <h2 id="comments-title">Bình luận</h2>
        </div>
        <span class="comments-panel__count">{{ $comments->count() }} bình luận</span>
    </div>

    @if(session('comment_success'))
        <div class="ui-alert ui-alert--success">{{ session('comment_success') }}</div>
    @endif

    @if($comments->isNotEmpty())
        <div class="comments-list">
            @foreach($comments as $comment)
                <article class="comment-item">
                    <div class="comment-item__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($comment->name, 0, 1)) }}</div>
                    <div>
                        <div class="comment-item__meta"><strong>{{ $comment->name }}</strong><time datetime="{{ $comment->created_at?->toIso8601String() }}">{{ $comment->created_at?->format('d/m/Y H:i') }}</time></div>
                        <p>{{ $comment->content }}</p>
                        @if($comment->replies->isNotEmpty())
                            <div class="comment-replies">
                                @foreach($comment->replies as $reply)
                                    <article class="comment-item comment-item--reply">
                                        <div class="comment-item__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($reply->name, 0, 1)) }}</div>
                                        <div><div class="comment-item__meta"><strong>{{ $reply->name }}</strong><time datetime="{{ $reply->created_at?->toIso8601String() }}">{{ $reply->created_at?->format('d/m/Y H:i') }}</time></div><p>{{ $reply->content }}</p></div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <p class="comments-empty">Chưa có bình luận nào. Hãy để lại góc nhìn đầu tiên.</p>
    @endif

    <form class="comments-form" action="{{ route('comments.store') }}" method="POST">
        @csrf
        <input type="hidden" name="commentable_type" value="{{ $commentableType }}">
        <input type="hidden" name="commentable_id" value="{{ $commentableId }}">
        <div class="grid gap-4 md:grid-cols-2">
            <div><label class="ui-label" for="comment-name">Họ và tên</label><input class="ui-input @error('name') border-red-500 @enderror" id="comment-name" name="name" value="{{ old('name') }}" required maxlength="120">@error('name')<div class="ui-error">{{ $message }}</div>@enderror</div>
            <div><label class="ui-label" for="comment-email">Email <span class="text-muted">(không bắt buộc)</span></label><input class="ui-input @error('email') border-red-500 @enderror" id="comment-email" name="email" type="email" value="{{ old('email') }}" maxlength="150">@error('email')<div class="ui-error">{{ $message }}</div>@enderror</div>
            <div class="md:col-span-2"><label class="ui-label" for="comment-content">Nội dung</label><textarea class="ui-input @error('content') border-red-500 @enderror" id="comment-content" name="content" rows="4" maxlength="5000" required>{{ old('content') }}</textarea>@error('content')<div class="ui-error">{{ $message }}</div>@enderror</div>
            <div class="md:col-span-2"><button class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" type="submit"><i class="fa-solid fa-message mr-2"></i>Gửi bình luận</button></div>
        </div>
    </form>
</section>
