<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('products', function (Blueprint $table) {
        // Penting: Harus ->nullable() biar user lama nilainya NULL (gak error)
        $table->timestamp('min_stock_alert')->nullable()->after('kategori');
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('min_stock_alert');
    });
}
};
