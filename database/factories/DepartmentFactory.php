<?php

namespace Database\Factories;

use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'faculty_id'  => Faculty::factory(),
            'name'        => $this->faker->words(2, true) . ' Department',
            'code'        => strtoupper($this->faker->unique()->lexify('???')),
            'description' => $this->faker->sentence(),
        ];
    }
}
