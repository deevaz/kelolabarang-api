<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StockInFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pemasok' => $this->faker->company,
            'catatan' => $this->faker->sentence,
            'tanggal_masuk' => Carbon::now(),
            'total_harga' => 0, // akan dihitung dari item
            'user_id' => 1, // nanti diganti di Seeder
        ];
    }
}
