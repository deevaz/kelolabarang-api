<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInItem extends Model
{
    use HasFactory;

    protected $table = 'stock_in_items';

    protected $fillable = [
        'nama',
        'harga',
        'jumlah_stok_masuk',
        // 'total_stok',
        'user_id',
    ];

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class, 'stock_in_id');
    }


}
