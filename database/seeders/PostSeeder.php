<?php

namespace Database\Seeders;

use App\Models\posts\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::whereEmail('test@example.com')->first();

        if (! $user) {
            return;
        }

        Post::factory(5)->create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
        ]);
    }
}
