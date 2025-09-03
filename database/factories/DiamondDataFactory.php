<?php

namespace Database\Factories;

use App\Models\DiamondData;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiamondDataFactory extends Factory
{
    protected $model = DiamondData::class;

    public function definition(): array
    {
        return [
            'upload_id' => Upload::factory(),
            'cut' => $this->faker->randomElement(['Round', 'Princess', 'Emerald']),
            'color' => $this->faker->randomElement(['D', 'E', 'F', 'G']),
            'clarity' => $this->faker->randomElement(['FL', 'IF', 'VVS1', 'VS1']),
            'carat_weight' => $this->faker->randomFloat(2, 0.5, 2.0),
            'total_sales_price' => $this->faker->numberBetween(1000, 10000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}