<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class StockOutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pembeli' => $this->faker->name,
            'catatan' => $this->faker->sentence,
            'tanggal_keluar' => Carbon::now(),
            'total_harga' => $this->faker->numberBetween(1000, 100000),
            'user_id' => 1, // bisa ganti jika perlu
        ];
    }
}
