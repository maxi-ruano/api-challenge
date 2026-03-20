<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Models\BranchModel;
use App\Infrastructure\Persistence\Models\EmployeeModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeModelFactory extends Factory
{
    protected $model = EmployeeModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'branch_id' => BranchModel::factory(),
        ];
    }
}