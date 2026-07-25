<?php

use App\Models\posts\Post;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the posts index page can be rendered', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->get(route('posts.index', $team->slug));

    $response->assertOk();
});

test('the posts index page shows posts', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    Post::factory()->count(3)->create([
        'team_id' => $team->id,
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('posts.index', $team->slug));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('posts/Index')
            ->has('posts', 3),
        );
});

test('the post create page can be rendered', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->get(route('posts.create', $team->slug));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('posts/Create'),
        );
});

test('posts can be created', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->post(route('posts.store', $team->slug), [
            'title' => 'Test Post Title',
            'body' => 'Test post body content.',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post Title',
        'team_id' => $team->id,
        'user_id' => $user->id,
    ]);
});

test('post creation requires title and body', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->post(route('posts.store', $team->slug), [
            'title' => '',
            'body' => '',
        ]);

    $response->assertSessionHasErrors(['title', 'body']);
});

test('the post edit page can be rendered', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    $post = Post::factory()->create([
        'team_id' => $team->id,
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('posts.edit', [$team->slug, $post]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('posts/Edit')
            ->where('post.id', $post->id),
        );
});

test('posts can be updated', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    $post = Post::factory()->create([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'title' => 'Original Title',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('posts.update', [$team->slug, $post]), [
            'title' => 'Updated Title',
            'body' => 'Updated body content.',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Updated Title',
    ]);
});

test('posts can be deleted', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    $post = Post::factory()->create([
        'team_id' => $team->id,
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('posts.destroy', [$team->slug, $post]));

    $response->assertRedirect();

    $this->assertSoftDeleted($post);
});

test('guests cannot access posts', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $this->get(route('posts.index', $team->slug))->assertRedirect(route('login'));
    $this->get(route('posts.create', $team->slug))->assertRedirect(route('login'));
    $this->post(route('posts.store', $team->slug), [])->assertRedirect(route('login'));
});
