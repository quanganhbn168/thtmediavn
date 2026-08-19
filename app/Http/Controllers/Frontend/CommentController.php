<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController
{
    /**
     * @var array<string, class-string>
     */
    private const TYPES = [
        'post' => Post::class,
        'service' => Service::class,
        'project' => Project::class,
        'page' => Page::class,
    ];

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commentable_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'commentable_id' => ['required', 'integer', 'min:1'],
            'parent_id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $modelClass = self::TYPES[$data['commentable_type']];
        $commentable = $modelClass::query()->whereKey($data['commentable_id'])->firstOrFail();

        if (in_array($modelClass, [Post::class, Service::class, Project::class, Page::class], true) && ! $commentable->is_active) {
            abort(404);
        }

        $parent = null;
        if (filled($data['parent_id'] ?? null)) {
            $parent = $commentable->comments()->whereKey($data['parent_id'])->first();
            abort_unless($parent, 422);
        }

        $commentable->commentableComments()->create([
            'parent_id' => $parent?->getKey(),
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'content' => $data['content'],
            'status' => 'pending',
        ]);

        return back()->with('comment_success', 'Bình luận đã được ghi nhận và sẽ hiển thị sau khi được duyệt.')->withFragment('binh-luan');
    }
}
