<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\posts\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ArticleController extends Controller
{
    function show(string $slug): Response
    {
        $post = Post::with(['user', 'team', 'comments.user', 'likes'])
            ->whereSlug($slug)
            ->firstOrFail();

        $related = Post::where('team_id', $post->team_id)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(5)
            ->get();

        return inertia('posts/Show', [
            'post' => $post,
            'related' => $related,
            'liked' => auth()->check() && $post->likes()->where('user_id', auth()->id())->exists(),
            'likesCount' => $post->likes()->count(),
        ]);
    }

    function comment(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate(['body' => 'required|string|max:1000']);

        $post = Post::whereSlug($slug)->firstOrFail();

        Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back();
    }

    function toggleLike(Request $request, string $slug): RedirectResponse
    {
        $post = Post::whereSlug($slug)->firstOrFail();
        $existing = Like::where('post_id', $post->id)->where('user_id', $request->user()->id)->first();

        if ($existing) {
            $existing->delete();
        } else {
            Like::create(['post_id' => $post->id, 'user_id' => $request->user()->id]);
        }

        return back();
    }
}
