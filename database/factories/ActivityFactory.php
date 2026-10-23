<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->word().' '.fake()->word()).' Club';

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => fake()->paragraph(),
            'day' => 'Tuesday',
            'time' => '7:00 PM - 9:00 PM',
        ];
    }

    public function draft(): static
    {
        return $this->state(['draft' => true]);
    }
}
