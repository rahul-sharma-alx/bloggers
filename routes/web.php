<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use App\Models\posts\Post;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Welcome', [
        'posts' => Post::with('user', 'team')->latest()->take(12)->get(),
    ]);
})->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::resource('posts', \App\Http\Controllers\posts\PostController::class)->except(['show']);
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';

Route::get('articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::post('articles/{slug}/comment', [ArticleController::class, 'comment'])->middleware('auth')->name('articles.comment');
Route::post('articles/{slug}/like', [ArticleController::class, 'toggleLike'])->middleware('auth')->name('articles.like');
