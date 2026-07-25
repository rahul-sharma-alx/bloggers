<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['post_id', 'user_id', 'body'];

    function post(): BelongsTo
    {
        return $this->belongsTo(posts\Post::class);
    }

    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
