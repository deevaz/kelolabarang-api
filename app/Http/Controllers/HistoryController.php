<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Support\Facades\Validator;

class HistoryController extends Controller
{
    public function getHistory($userId)
    {
        $stockIn = StockIn::with('items')->where('user_id', $userId)->orderBy('tanggal_masuk', 'DESC')->get();
        $stockOut = StockOut::with('items')->where('user_id', $userId)->orderBy('tanggal_keluar', 'DESC')->get();

        $data = [];

        if (!$stockIn->isEmpty()) {
            $data = array_merge($data, $stockIn->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'pemasok' => $item->pemasok,
                    'userId' => (string) $item->user_id,
                    'catatan' => $item->catatan,
                    'total_harga' => $item->total_harga,
                    'tanggal_masuk' => $item->tanggal_masuk,
                    'tanggal' => $item->tanggal_masuk,
                    'tipe' => 'masuk',
                    // 'isStokMasuk' => true,
                    // 'isStokKeluar' => false,
                    'total_masuk' => $item->items->sum('jumlah_stok_masuk'),
                    'barang' => $item->items->map(function ($barang) {
                        return [
                            'nama' => $barang->nama,
                            'harga' => $barang->harga,
                            'jumlah_stok_masuk' => $barang->jumlah_stok_masuk,
                            // 'total_stok' => $barang->total_stok,
                        ];
                    }),
                ];
            })->toArray());
        }


        if (!$stockOut->isEmpty()) {
            $data = array_merge($data, $stockOut->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'pembeli' => $item->pembeli,
                    'userId' => (string) $item->user_id,
                    'catatan' => $item->catatan,
                    'total_harga' => $item->total_harga,
                    'tanggal_keluar' => $item->tanggal_keluar,
                    'tanggal' => $item->tanggal_keluar,
                    'tipe' => 'keluar',
                    // 'isStokMasuk' => false,
                    // 'isStokKeluar' => true,
                    'total_keluar' => $item->items->sum('jumlah_stok_keluar'),
                    'barang' => $item->items->map(function ($barang) {
                        return [
                            'nama' => $barang->nama,
                            'harga' => $barang->harga,
                            'jumlah_stok_keluar' => $barang->jumlah_stok_keluar,
                            // 'total_stok' => $barang->total_stok,
                        ];
                    }),
                ];
            })->toArray());
        }

        if (empty($data)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 204);
        }
        $data = collect($data)->sortByDesc('tanggal')->values()->all();

        return response()->json($data, 200);
    }


  public function getFilteredHistory(Request $request, $userId)
    {
        $rules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $stockOut = StockOut::with('items')
            ->where('user_id', $userId)
            ->whereBetween('tanggal_keluar', [$startDate, $endDate])
            ->get();

        $stockIn = StockIn::with('items')
            ->where('user_id', $userId)
            ->whereBetween('tanggal_masuk', [$startDate, $endDate])
            ->get();


        $data = collect();

        $stockOutData = $stockOut->map(function ($item) {
            return [
                'tanggal' => $item->tanggal_keluar,
                'tipe' => 'keluar',
                // 'isStokMasuk' => false,
                // 'isStokKeluar' => true,
                'id' => (string) $item->id,
                'pemasok' => null,
                'pembeli' => $item->pembeli,
                'userId' => (string) $item->user_id,
                'catatan' => $item->catatan,
                'total_harga' => $item->total_harga,
                'tanggal_masuk' => null,
                'tanggal_keluar' => $item->tanggal_keluar,
                'total_masuk' => null,
                'total_keluar' => $item->items->sum('jumlah_stok_keluar'),
                'barang' => $item->items->map(function ($barang) {
                    return [
                        'nama' => $barang->nama,
                        'harga' => $barang->harga,
                        'jumlah_stok_masuk' => null,
                        'jumlah_stok_keluar' => $barang->jumlah_stok_keluar,
                        // 'total_stok' => $barang->total_stok,
                    ];
                }),
            ];
        });


        $stockInData = $stockIn->map(function ($item) {
            return [
                'tanggal' => $item->tanggal_masuk,
                'tipe' => 'masuk',
                // 'isStokMasuk' => true,
                // 'isStokKeluar' => false,
                'id' => (string) $item->id,
                'pemasok' => $item->pemasok,
                'pembeli' => null,
                'userId' => (string) $item->user_id,
                'catatan' => $item->catatan,
                'total_harga' => $item->total_harga,
                'tanggal_masuk' => $item->tanggal_masuk,
                'tanggal_keluar' => null,
                'total_masuk' => $item->items->sum('jumlah_stok_masuk'),
                'total_keluar' => null,
                'barang' => $item->items->map(function ($barang) {
                    return [
                        'nama' => $barang->nama,
                        'harga' => $barang->harga,
                        'jumlah_stok_masuk' => $barang->jumlah_stok_masuk,
                        'jumlah_stok_keluar' => null,
                        // 'total_stok' => $barang->total_stok,
                    ];
                }),
            ];
        });


        $data = $stockOutData->merge($stockInData)
                ->sortByDesc('tanggal')
                ->values();

        return response()->json($data, 200);
    }
}
