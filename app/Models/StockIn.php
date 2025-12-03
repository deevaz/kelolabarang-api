<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockInItem;

class StockIn extends Model
{
    use HasFactory;

    protected $table = 'stock_in';

    protected $fillable = [
        'pemasok',
        'catatan',
        'tanggal_masuk',
        'total_harga',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(StockInItem::class);
    }
}
