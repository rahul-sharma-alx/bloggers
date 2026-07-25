<?php

namespace App\Http\Controllers\posts;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\posts\Post;
use Inertia\Response;

class PostController extends Controller
{
    private function team(Request $request): Team
    {
        $slug = $request->route('current_team');

        return is_string($slug) ? Team::whereSlug($slug)->firstOrFail() : $slug;
    }

    function index(Request $request): Response
    {
        $team = $this->team($request);
        $posts = Post::getPostsByTeamId($team->id);

        return inertia('posts/Index', ['posts' => $posts]);
    }

    function create(): Response
    {
        return inertia('posts/Create');
    }

    function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'slug' => 'nullable|string|max:255',
            'featured_image' => 'nullable|string|max:2048',
        ]);

        $team = $this->team($request);
        $post = (new Post())->createPost($team->id, $request->user()->id, $validated['title'], $validated['body'], $validated['slug'] ?? null);

        if ($validated['featured_image'] ?? null) {
            $post->update(['featured_image' => $validated['featured_image']]);
        }

        return redirect()->route('posts.index', $team->slug);
    }

    function edit(Request $request): Response
    {
        $post = Post::findOrFail($request->route('post'));

        return inertia('posts/Edit', ['post' => $post]);
    }

    function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'slug' => 'nullable|string|max:255',
            'featured_image' => 'nullable|string|max:2048',
        ]);

        $post = Post::findOrFail($request->route('post'));
        $post->updatePost($validated['title'], $validated['body'], $validated['slug'] ?? null);
        $post->update(['featured_image' => $validated['featured_image'] ?? null]);

        return redirect()->route('posts.index', $this->team($request)->slug);
    }

    function destroy(Request $request): RedirectResponse
    {
        $post = Post::findOrFail($request->route('post'));
        $post->deletePost($post);

        return redirect()->route('posts.index', $this->team($request)->slug);
    }
}
