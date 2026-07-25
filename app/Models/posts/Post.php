<?php

namespace App\Models\posts;

use Illuminate\Database\Eloquent\Model;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
#[Fillable(['team_id', 'user_id', 'title', 'slug', 'body', 'featured_image'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;


    function comments(): HasMany
    {
        return $this->hasMany(\App\Models\Comment::class);
    }

    function likes(): HasMany
    {
        return $this->hasMany(\App\Models\Like::class);
    }

    function createPost($teamId, $userId, $title, $body, $slug = null)
    {
        $post = new Post();
        $post->team_id = $teamId;
        $post->user_id = $userId;
        $post->title = $title;
        $post->slug = $slug ?: $this->createSlug($title);
        $post->body = $body;
        $post->save();

        return $post;
    }

    function updatePost($title, $body, $slug = null)
    {
        $this->title = $title;
        $this->slug = $slug ?: $this->createSlug($title);
        $this->body = $body;
        $this->save();

        return $this;
    }

    function deletePost($post)
    {
        $post->delete();
    }

    function restorePost($post)
    {
        $post->restore();
    }

    function forceDeletePost($post)
    {
        $post->forceDelete();
    }

    static function getPostById($postId)
    {
        return Post::find($postId);
    }

    static function getAllPosts()
    {
        return Post::all();
    }

    static function getPostsByTeamId($teamId)
    {
        return Post::where('team_id', $teamId)->get();
    }

    static function getPostsByUserId($userId)
    {
        return Post::where('user_id', $userId)->get();
    }

    static function getPostsByTeamIdAndUserId($teamId, $userId)
    {
        return Post::where('team_id', $teamId)->where('user_id', $userId)->get();
    }

    static function getPostsBySlug($slug)
    {
        return Post::where('slug', $slug)->first();
    }

    static function getPostsByTitle($title)
    {
        return Post::where('title', 'like', '%' . $title . '%')->get();
    }

    function createSlug($title)
    {
        return Str::slug($title);
    }
    /**
     * Get the team that the post belongs to.
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user that the post belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
