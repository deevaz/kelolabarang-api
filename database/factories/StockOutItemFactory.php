<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockOutItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'stock_out_id' => null, // Akan di-set pada Seeder
            'nama' => $this->faker->word,
            'harga' => $this->faker->numberBetween(1000, 100000),
            'jumlah_stok_keluar' => $this->faker->numberBetween(1, 10),
            // 'total_stok' => $this->faker->numberBetween(10, 50),
            'user_id' => 1, // Bisa ganti jika perlu
        ];
    }
}
