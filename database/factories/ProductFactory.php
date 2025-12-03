<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_barang' => $this->faker->unique()->word,
            'nama_barang' => $this->faker->word,
            'stok_awal' => $this->faker->numberBetween(10, 100),
            'harga_beli' => $this->faker->numberBetween(10000, 100000),
            'harga_jual' => $this->faker->numberBetween(15000, 120000),
            'kadaluarsa' => Carbon::now()->addMonths(12),
            'deskripsi' => $this->faker->sentence,
            'gambar' => $this->faker->imageUrl(),
            'kategori' => $this->faker->word,
            'total_stok' => $this->faker->numberBetween(50, 200),
            'user_id' => 1, // bisa ganti jika perlu
        ];
    }
}
