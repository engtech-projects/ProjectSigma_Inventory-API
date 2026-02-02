<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('price_quotation_items', function (Blueprint $table) {
            $table->string('actual_brand')->nullable()->change();
            $table->decimal('unit_price', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('price_quotation_items')
        ->whereNull('actual_brand')
        ->update(['actual_brand' => '']);
        DB::table('price_quotation_items')
        ->whereNull('unit_price')
        ->update(['unit_price' => 0]);
        Schema::table('price_quotation_items', function (Blueprint $table) {
            $table->string('actual_brand')->nullable(false)->change();
            $table->decimal('unit_price', 12, 2)->nullable(false)->change();
        });
    }
};
