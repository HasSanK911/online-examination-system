<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'department_id' => Department::factory(),
            'student_id'    => 'STU-' . $this->faker->unique()->numerify('####'),
            'roll_number'   => strtoupper($this->faker->unique()->lexify('??-####')),
            'semester'      => $this->faker->numberBetween(1, 8),
            'batch'         => '2021-2025',
            'phone'         => $this->faker->phoneNumber(),
            'status'        => 'active',
        ];
    }
}
