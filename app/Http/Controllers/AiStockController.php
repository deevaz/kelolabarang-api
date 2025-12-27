<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Carbon\Carbon;

class AiStockController extends Controller
{
    public function predict(Request $request)
    {
        // Parameter input: nama barang (karena relasi tabelmu pakai nama)
        $namaBarang = $request->query('nama_barang'); 

        if (!$namaBarang) {
            return response()->json(['error' => 'Nama barang wajib diisi'], 400);
        }

        // --- A. HITUNG STOK REAL-TIME ---
        // Rumus: Total Masuk - Total Keluar
        $totalMasuk = DB::table('stock_in_items')
            ->where('nama', $namaBarang)
            ->sum('jumlah_stok_masuk');

        $totalKeluar = DB::table('stock_out_items')
            ->where('nama', $namaBarang)
            ->sum('jumlah_stok_keluar');

        $stokSekarang = $totalMasuk - $totalKeluar;

        if ($stokSekarang <= 0) {
            return response()->json([
                'status' => 'habis',
                'stok_sekarang' => 0,
                'pesan' => 'Stok sudah habis.',
                'color' => 'red' // Kode warna buat Flutter
            ]);
        }

        // --- B. SIAPKAN DATA BUAT AI ---
        // Ambil data penjualan 30 hari terakhir
        $history = DB::table('stock_out_items as item')
            ->join('stock_out as head', 'item.stock_out_id', '=', 'head.id')
            ->where('item.nama', $namaBarang)
            ->where('head.tanggal_keluar', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(head.tanggal_keluar) as tanggal, SUM(item.jumlah_stok_keluar) as total_qty')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Kalau data sedikit, return info standar
        if ($history->count() < 3) {
            return response()->json([
                'status' => 'info',
                'stok_sekarang' => $stokSekarang,
                'pesan' => 'Butuh lebih banyak data transaksi untuk prediksi AI.',
                'prediksi_hari' => '-',
                'color' => 'grey'
            ]);
        }

        // --- C. FORMAT DATA JSON ---
        // Ubah tanggal jadi angka urutan (Hari 1, Hari 2, dst) biar bisa dibaca Regresi Linear
        $formattedData = [];
        $firstDate = Carbon::parse($history->first()->tanggal);

        foreach ($history as $row) {
            $date = Carbon::parse($row->tanggal);
            $dayDiff = $firstDate->diffInDays($date) + 1;
            
            $formattedData[] = [
                'hari' => $dayDiff,
                'qty' => (int) $row->total_qty
            ];
        }

        // --- D. PANGGIL PYTHON SCRIPT ---
        $scriptPath = storage_path('app/ai/predict_stock.py');
        $inputJson = json_encode($formattedData);

        // Command line: python3 [path_script] [json_data]
        $process = new Process(['python3', $scriptPath, $inputJson]);
        $process->run();

        // Cek error script
        if (!$process->isSuccessful()) {
            // Ini akan muncul kalau server belum install python/pandas
            throw new ProcessFailedException($process);
        }

        // --- E. HASIL AKHIR ---
        $outputAi = json_decode($process->getOutput(), true);
        
        if ($outputAi['status'] == 'success') {
            $burnRate = $outputAi['rate_harian']; // Rata-rata laku per hari
            
            // Rumus Prediksi: Stok Sekarang / Laju Penjualan
            $sisaHari = floor($stokSekarang / $burnRate);

            // Logika UI (Warna status)
            $color = 'green';
            if ($sisaHari < 3) $color = 'red';          // Kritis
            elseif ($sisaHari < 7) $color = 'orange';   // Waspada

            return response()->json([
                'status' => 'success',
                'stok_sekarang' => $stokSekarang,
                'laku_per_hari' => $burnRate . ' pcs',
                'prediksi_habis' => $sisaHari . ' hari lagi',
                'pesan_ai' => $outputAi['pesan'],
                'color' => $color
            ]);
        }

        return response()->json($outputAi);
    }
}