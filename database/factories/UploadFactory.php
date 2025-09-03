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

// ========================================

// database/factories/UploadFactory.php

namespace Database\Factories;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UploadFactory extends Factory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        $uuid = $this->faker->uuid;
        return [
            'user_id' => User::factory(),
            'filename' => 'upload_' . $uuid . '.csv',
            'original_filename' => 'diamonds.csv',
            'file_path' => 'uploads/upload_' . $uuid . '.csv',
            'file_size' => $this->faker->numberBetween(1024, 1048576),
            'status' => 'completed',
            'total_records' => $this->faker->numberBetween(100, 1000),
            'processed_records' => function (array $attributes) {
                return $attributes['total_records'];
            },
            'successful_records' => function (array $attributes) {
                return $attributes['total_records'];
            },
            'failed_records' => 0,
            'progress_percentage' => 100.0,
            'error_message' => null,
            'started_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}