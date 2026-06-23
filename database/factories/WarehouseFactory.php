<?php
namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;
public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('WH[0-9]{3}'),
            'name' => $this->faker->words(2, true) . ' Warehouse',
            'location' => $this->faker->address(),
            'is_active' => true,
        ];
    }
}