<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\StockOut;
use App\Models\StockOutItem;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class StockOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($userId)
    {
        $stockOut = StockOut::with('items')->where('user_id', $userId)->orderBy('created_at', 'DESC')->get();
        if ($stockOut->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data stock-out tidak ditemukan'
            ], 204);
        }
        $data = $stockOut->map(function ($item) {
            return [
                'id' => (string) $item->id,
                'pembeli' => $item->pembeli,
                'userId' => (string) $item->user_id,
                'catatan' => $item->catatan,
                'total_harga' => $item->total_harga,
                'tanggal_keluar' => $item->tanggal_keluar,
                'total_keluar' => $item->items->sum('jumlah_stok_keluar'),
                'barang' => $item->items->map(function ($barang) {
                    return [
                        'nama' => $barang->nama,
                        'harga' => $barang->harga,
                        'jumlah_stok_keluar' => $barang->jumlah_stok_keluar,
                    ];
                }),
            ];
        });

        return response()->json($data, 200);
    }


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

        $stockOut = StockOut::with('items')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->whereBetween('tanggal_keluar', [$startDate, $endDate])
            ->get();

        if ($stockOut->isEmpty()) {
            return response()->json([
                'kode' => 204,
                'status' => 'error',
                'message' => 'Data stock-out tidak ditemukan dalam rentang tanggal'
            ], 204);
        }

        $data = $stockOut->map(function ($item) {
            return [
                'id' => (string) $item->id,
                'pembeli' => $item->pembeli,
                'userId' => (string) $item->user_id,
                'catatan' => $item->catatan,
                'total_harga' => $item->total_harga,
                'tanggal_keluar' => $item->tanggal_keluar,
                'total_keluar' => $item->items->sum('jumlah_stok_keluar'),
                'barang' => $item->items->map(function ($barang) {
                    return [
                        'nama' => $barang->nama,
                        'harga' => $barang->harga,
                        'jumlah_stok_keluar' => $barang->jumlah_stok_keluar,
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
            'pembeli' => 'required',
            'total_harga' => 'required|numeric',
            'tanggal_keluar' => 'required|date',
            'catatan' => 'nullable|string|max:255',

            'barang' => 'required|array',
            'barang.*.nama' => 'required|string|max:255',
            'barang.*.harga' => 'required|numeric',
            'barang.*.jumlah_stok_keluar' => 'required|numeric',

        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Tidak Valid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = new StockOut();
            $data->pembeli = $request->pembeli;
            $data->catatan = $request->catatan;
            $data->tanggal_keluar = $request->tanggal_keluar;
            $data->total_harga = $request->total_harga;
            $data->user_id = $userId;
            $data->save();

            $barangData = [];
            foreach ($request->barang as $item) {
                $product = Product::where('nama_barang', $item['nama'])->first();

                if ($product) {
                    $product->decrement('stok', $item['jumlah_stok_keluar']);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Barang tidak ditemukan: ' . $item['nama']
                    ], 404);
                }
                $barangData[] = new StockOutItem([
                    'stock_out_id' => $data->id,
                    'nama' => $item['nama'],
                    'harga' => $item['harga'],
                    'jumlah_stok_keluar' => $item['jumlah_stok_keluar'],
                    'user_id' => $userId,
                ]);
            }

            $data->items()->saveMany($barangData);

            DB::commit();

            $responseData = [
                'id' => (string) $data->id,
                'pembeli' => $data->pembeli,
                'catatan' => $data->catatan,
                'tanggal_keluar' => $data->tanggal_keluar,
                'total_harga' => $data->total_harga,
                'barang' => $data->items->map(function ($barang) {
                    return [
                        'nama' => $barang->nama,
                        'harga' => $barang->harga,
                        'jumlah_stok_keluar' => $barang->jumlah_stok_keluar,
                    ];
                }),
            ];
            return response()->json([
                'status' => 'success',
                'message' => 'Stock-out berhasil ditambahkan',
                'data' => $responseData
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan stock-out',
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
    public function destroy($userId, $id)
    {
        $stockIn = StockOut::where('user_id', $userId)->where('id', $id)->first();

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
