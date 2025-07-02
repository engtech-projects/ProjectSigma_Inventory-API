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
            $table->renameColumn('brand', 'actual_brand');
            $table->renameColumn('price', 'unit_price');
            $table->renameColumn('remarks', 'remarks_during_canvass');
        });

        Schema::table('price_quotation_items', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('remarks_during_canvass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_quotation_items', function (Blueprint $table) {
            $table->renameColumn('actual_brand', 'brand');
            $table->renameColumn('unit_price', 'price');
            $table->renameColumn('remarks_during_canvass', 'remarks');
        });
    }
};
