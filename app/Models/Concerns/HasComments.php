<?php

namespace App\Models\Concerns;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    public function commentableComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function comments(): MorphMany
    {
        return $this->commentableComments()
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->oldest();
    }
}
