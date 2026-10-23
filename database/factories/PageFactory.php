<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        $title = Str::title(rtrim(fake()->unique()->sentence(3, false), '.'));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => fake()->paragraph(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['draft' => true]);
    }
}
