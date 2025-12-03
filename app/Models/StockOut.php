<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockOutItem;

class StockOut extends Model
{
    use HasFactory;

    protected $table = 'stock_out';

    protected $fillable = [
        'pembeli',
        'catatan',
        'tanggal_keluar',
        'total_harga',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(StockOutItem::class);
    }
}
