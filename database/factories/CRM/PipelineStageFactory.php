<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Pipeline;
use App\Models\CRM\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PipelineStage>
 */
class PipelineStageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->word();

        return [
            'pipeline_id' => Pipeline::factory(),
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'position' => fake()->numberBetween(0, 10),
            'probability' => fake()->numberBetween(10, 90),
            'color' => fake()->hexColor(),
            'is_won' => false,
            'is_lost' => false,
        ];
    }

    public function won(): self
    {
        return $this->state(['is_won' => true, 'is_lost' => false, 'probability' => 100]);
    }

    public function lost(): self
    {
        return $this->state(['is_won' => false, 'is_lost' => true, 'probability' => 0]);
    }
}
