<?php

namespace Database\Factories\CRM;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Account;
use App\Models\CRM\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'assigned_to' => null,
            'created_by' => null,
            'subject_type' => null,
            'subject_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'due_at' => fake()->optional(0.8)->dateTimeBetween('-5 days', '+30 days'),
            'completed_at' => null,
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'status' => TaskStatus::Pending,
        ];
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function highPriority(): self
    {
        return $this->state(['priority' => TaskPriority::High]);
    }
}
