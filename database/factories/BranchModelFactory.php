<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Models\BranchModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchModelFactory extends Factory
{
    protected $model = BranchModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
        ];
    }
}