<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_stocks_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')
                ->constrained('setup_warehouses', 'id', 'wss_warehouse')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('item_id')
                ->constrained('item_profile', 'id', 'wss_item')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->decimal('total_quantity', 10, 2)
                ->comment('Total quantity of the item in the warehouse');
            $table->foreignId('uom_id')
                ->constrained('setup_uom', 'id', 'wss_uom')
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Unit of measure for the item in the summary');
            $table->json('uom_conversion')
                ->nullable()
                ->comment('Custom conversion factor for allowing cross UOM group conversions.');
            $table->json('metadata')
                ->comment('Additional metadata for the summary, such as last updated, user, etc.');
            $table->timestamps();
            $table->unique(['warehouse_id', 'item_id'], 'wss_unique_warehouse_item');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks_summaries');
    }
};
