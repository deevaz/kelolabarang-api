<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockInItemFactory extends Factory
{
    public function definition(): array
    {
        $jumlah = $this->faker->numberBetween(1, 20);
        $harga = $this->faker->numberBetween(1000, 100000);
        return [
            'nama' => $this->faker->word,
            'harga' => $harga,
            'jumlah_stok_masuk' => $jumlah,
            // 'total_stok' => $jumlah, // anggap baru masuk semua
            'user_id' => 1, // nanti diganti di Seeder
            'stock_in_id' => null, // di-set di Seeder
        ];
    }
}
