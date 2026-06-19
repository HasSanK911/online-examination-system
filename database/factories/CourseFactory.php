<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'title'         => $this->faker->words(3, true),
            'code'          => strtoupper($this->faker->unique()->lexify('??-###')),
            'credit_hours'  => $this->faker->numberBetween(2, 4),
            'semester'      => $this->faker->numberBetween(1, 8),
            'status'        => 'active',
        ];
    }
}
