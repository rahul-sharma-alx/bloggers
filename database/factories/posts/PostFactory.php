<?php

namespace Database\Factories\posts;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence();

        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title, '_'),
            'body' => fake()->paragraphs(3, true),
        ];
    }
}
