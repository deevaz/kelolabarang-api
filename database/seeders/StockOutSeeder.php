<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\User;

class StockOutSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        StockOut::factory()
            ->count(10) // Menambahkan 10 transaksi keluar
            ->create(['user_id' => $user->id])
            ->each(function ($stockOut) use ($user) {
                $items = StockOutItem::factory()
                    ->count(rand(1, 3)) // Bisa menambahkan antara 1 hingga 3 item per transaksi
                    ->make(['user_id' => $user->id]);

                $totalHarga = 0;

                $items->each(function ($item) use ($stockOut, &$totalHarga) {
                    $item->stock_out_id = $stockOut->id;
                    $item->save();

                    $totalHarga += $item->harga * $item->jumlah_stok_keluar;
                });

                $stockOut->total_harga = $totalHarga;
                $stockOut->save();
            });
    }
}
