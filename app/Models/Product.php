<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'stok',
        'harga_beli',
        'harga_jual',
        'kadaluarsa',
        'deskripsi',
        'vendor',
        'gambar',
        'kategori',
        'user_id',
    ];
}
