<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($userId)
    {
        $products = Product::where('user_id', $userId)->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diambil',
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $userId)
    {
        $data = $request->validate([
            'kode_barang' => 'nullable|string',
            'nama_barang' => 'required|string',
            'stok' => 'nullable|integer',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'kadaluarsa' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'kategori' => 'nullable|string',
        ]);
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('gambar_barang', 'public');
            $data['gambar'] = 'https://kelola.abdaziz.my.id/storage/' . $gambarPath;
        }

        $data['user_id'] = $userId;

        $product = Product::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($userId, $id)
    {
        $product = Product::where('user_id', $userId)->where('id', $id)->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Produk berhasil diambil',
                'data' => $product
            ], 200
        );
    }


    // Show product by category
    public function showByCategory($userId, $category)
    {
        $products = Product::where('user_id', $userId)->where('kategori', $category)->orderBy('created_at', 'DESC')->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Kategori {$category} berhasil diambil",
            'data' => $products
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $userId, $id)
    {
        $product = Product::where('user_id', $userId)->where('id', $id)->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'], 404);
        }

        $data = $request->validate([
            'kode_barang' => 'nullable|string',
            'nama_barang' => 'required|string',
            'stok' => 'nullable|integer',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'kadaluarsa' => 'required|date',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'kategori' => 'required|string',

        ]);

        $gambarPath = null;

        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('gambar_barang', 'public');
            $data['gambar'] = 'https://kelola.abdaziz.my.id/storage/' . $gambarPath;
        }
        $product = Product::find($request->id);

        if ($product) {
            $product->kode_barang = $request->kode_barang;
            $product->nama_barang = $request->nama_barang;
            $product->stok = $request->stok;
            $product->harga_beli = $request->harga_beli;
            $product->harga_jual = $request->harga_jual;
            $product->kadaluarsa = $request->kadaluarsa;
            $product->deskripsi = $request->deskripsi;
            if ($gambarPath) {
                $product->gambar = 'https://kelola.abdaziz.my.id/storage/' . $gambarPath;
            }
            $product->kategori = $request->kategori;

            $product->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil diperbarui',
                'data' => $product
            ], 200);
        } else {
        return response()->json([
            'status' => 'error',
            'message' => 'Produk tidak ditemukan'], 404);}
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($userId, $id)
    {
        $product = Product::where('user_id', $userId)->where('id', $id)->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus']);
    }

    // Profit
    public function getProfit($userId)
    {
        $products = Product::where('user_id', $userId)->get();

        $totalProfit = 0;
        $profitDetails = [];

        foreach ($products as $product) {
            $profitPerItem = $product->harga_jual - $product->harga_beli;
            $totalProductProfit = $profitPerItem * $product->stok;

            $profitDetails[] = [
                'nama_barang' => $product->nama_barang,
                'profit_per_item' => $profitPerItem,
                'stok' => $product->stok,
                'total_profit' => $totalProductProfit,
            ];

            $totalProfit += $totalProductProfit;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data profit berhasil diambil',
            'total_profit' => $totalProfit,
            'data' => $profitDetails
        ], 200);
    }

    // Profit by date range
    public function getProfitByDate($userId, $startDate, $endDate)
    {
        $products = Product::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalProfit = 0;
        $profitDetails = [];

        foreach ($products as $product) {
            $profitPerItem = $product->harga_jual - $product->harga_beli;
            $totalProductProfit = $profitPerItem * $product->total_stok;

            $profitDetails[] = [
                'nama_barang' => $product->nama_barang,
                'profit_per_item' => $profitPerItem,
                'total_stok' => $product->total_stok,
                'total_profit' => $totalProductProfit,
            ];

            $totalProfit += $totalProductProfit;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data profit berdasarkan tanggal berhasil diambil',
            'total_profit' => $totalProfit,
            'data' => $profitDetails
        ], 200);
    }
}
