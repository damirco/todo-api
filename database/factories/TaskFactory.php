<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3), // Случайное название из трёх слов
            'description' => $this->faker->paragraph(), // Случайный текст
            'due_date' => $this->faker->dateTimeBetween('-1 months', '+3 month'),
            'priority' => $this->faker->randomElement([TaskPriority::LOW, TaskPriority::MEDIUM, TaskPriority::HIGH]),
            'category_id' => Category::factory(),
            'status' => $this->faker->randomElement([TaskStatus::COMPLETED, TaskStatus::NOT_COMPLETED]),
        ];
    }
}
