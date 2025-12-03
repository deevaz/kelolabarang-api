<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StockIn;
use App\Models\StockInItem;

class StockInSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        StockIn::factory()
            ->count(10)
            ->create(['user_id' => $user->id])
            ->each(function ($stockIn) use ($user) {
                $items = StockInItem::factory()
                    ->count(rand(1, 3))
                    ->make(['user_id' => $user->id]);

                $totalHarga = 0;

                $items->each(function ($item) use ($stockIn, &$totalHarga) {
                    $item->stock_in_id = $stockIn->id;
                    $item->save();

                    $totalHarga += $item->harga * $item->jumlah_stok_masuk;
                });

                $stockIn->total_harga = $totalHarga;
                $stockIn->save();
            });
    }
}
