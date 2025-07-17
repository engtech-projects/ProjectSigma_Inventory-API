<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('price_quotation_items', function (Blueprint $table) {
            $table->string('actual_brand')->nullable(false)->change();
            $table->decimal('unit_price', 12, 2)->nullable(false)->change();
        });
    }
};
