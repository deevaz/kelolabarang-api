<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutItem extends Model
{
    use HasFactory;

    protected $table = 'stock_out_items';

    protected $fillable = [
        'nama',
        'harga',
        'jumlah_stok_keluar',
        'user_id',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class, 'stock_out_id');
    }

}
