<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FacultyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'      => $this->faker->words(3, true) . ' Faculty',
            'code'      => strtoupper($this->faker->unique()->lexify('??')),
            'dean_name' => $this->faker->name(),
            'status'    => 'active',
        ];
    }
}
