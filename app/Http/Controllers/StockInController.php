<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockIn;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\StockInItem;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Carbon\Carbon;

class StockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($userId)
    {
        $stockIn = StockIn::with('items')->where('user_id', $userId)->orderBy('created_at', 'DESC')->get();

        if ($stockIn->isEmpty()) {
            return response()->json([
                'kode' => 204,
                'status' => 'error',
                'message' => 'Data stock-in tidak ditemukan'
            ], 204);
        }

        $data = $stockIn->map(function ($item) {
            return [
                'id' => (string) $item->id,
                'pemasok' => $item->pemasok,
                'userId' => (string) $item->user_id,
                'catatan' => $item->catatan,
                'total_harga' => $item->total_harga,
                'tanggal_masuk' => $item->tanggal_masuk,
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
        });

        return response()->json($data, 200);
    }


    /**
     * Display a listing of the resource based on a date range.
     */
    public function getByDateRange(Request $request, $userId)
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

        $stockIn = StockIn::with('items')
            ->orderBy('created_at', 'DESC')
            ->where('user_id', $userId)
            ->whereBetween('tanggal_masuk', [$startDate, $endDate])
            ->get();

        if ($stockIn->isEmpty()) {
            return response()->json([
                'kode' => 204,
                'status' => 'error',
                'message' => 'Data stock-in tidak ditemukan dalam rentang tanggal'
            ], 204);
        }

        $data = $stockIn->map(function ($item) {
            return [
                'id' => (string) $item->id,
                'pemasok' => $item->pemasok,
                'userId' => (string) $item->user_id,
                'catatan' => $item->catatan,
                'total_harga' => $item->total_harga,
                'tanggal_masuk' => $item->tanggal_masuk,
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
        });

        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $userId)
    {

    $rules = [
        'pemasok' => 'required|string|max:255',
        'catatan' => 'nullable|string|max:255',
        'tanggal_masuk' => 'required|date',
        'total_harga' => 'required|numeric',
        'barang' => 'required|array',
        'barang.*.nama' => 'required|string|max:255',
        'barang.*.harga' => 'required|numeric',
        'barang.*.jumlah_stok_masuk' => 'required|integer',
        // 'barang.*.total_stok' => 'nullable|integer',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Data tidak valid',
            'errors' => $validator->errors()
        ], 422);
    }

    // Begin database transaction
    DB::beginTransaction();

    try {
        // Create new StockIn record
        $stockIn = new StockIn();
        $stockIn->pemasok = $request->pemasok;
        $stockIn->catatan = $request->catatan;
        $stockIn->tanggal_masuk = $request->tanggal_masuk;
        $stockIn->total_harga = $request->total_harga;
        $stockIn->user_id = $userId;
        $stockIn->save();

        // Prepare array to hold StockInItem
        $barangData = [];

        // Process each incoming barang item
        foreach ($request->barang as $item) {
            // Find the product by nama
            $product = Product::where('nama_barang', $item['nama'])->first();

            if ($product) {
                // Update the total_stok by incrementing with jumlah_stok_masuk
                $product->increment('stok', $item['jumlah_stok_masuk']);
                // Log the stock-in update for the product
                Storage::append('logs/stock_in_updates.log', '[' . now() . '] Product updated: ' . $product->nama_barang . ', Incremented by: ' . $item['jumlah_stok_masuk']);
            } else {
                // Handle the case where the product does not exist
                // For example, you might create a new product or log a warning
                // Product::create([
                //     'nama' => $item['nama'],
                //     'total_stok' => $item['jumlah_stok_masuk'],
                //     // other necessary fields
                // ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                    'product_name' => $item['nama']
                ], 404);

                // Log the error for debugging purposes
                Storage::append('error_logs/stock_in_errors.log', '[' . now() . '] Product not found: ' . $item['nama']);
                printf("Product %s not found. Please check the name.\n", $item['nama']);
            }

            // Add to barangData array
            $barangData[] = new StockInItem([
                'nama' => $item['nama'],
                'harga' => $item['harga'],
                'jumlah_stok_masuk' => $item['jumlah_stok_masuk'],
                // 'total_stok' => $item['total_stok'],
                'user_id' => $userId,
            ]);
        }

        // Save all StockInItem
        $stockIn->items()->saveMany($barangData);

        // Commit the transaction
        DB::commit();

        // Prepare response data
        $responseData = [
            'barang' => $stockIn->items->map(function ($barang) {
                return [
                    'nama' => $barang->nama,
                    'harga' => $barang->harga,
                    'jumlah_stok_masuk' => $barang->jumlah_stok_masuk,
                    // 'total_stok' => $barang->total_stok,
                ];
            }),
            'tanggal_masuk' => $stockIn->tanggal_masuk,
            'pemasok' => $stockIn->pemasok,
            'catatan' => $stockIn->catatan,
            'total_harga' => $stockIn->total_harga,
            'id' => (string) $stockIn->id,
            'userId' => (string) $stockIn->user_id,
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Stock-in berhasil ditambahkan',
            'data' => $responseData
        ], 201);

    } catch (\Exception $e) {
        // Rollback the transaction in case of error
        DB::rollBack();

        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat menambahkan stock-in',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($userId,  $id)
    {
        $stockIn = StockIn::where('user_id', $userId)->where('id', $id)->first();

        if (!$stockIn) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok tidak ditemukan'
            ], 404);
        }

        $stockIn->items()->delete();
        $stockIn->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Stok berhasil dihapus'
        ], 200);
    }
}
