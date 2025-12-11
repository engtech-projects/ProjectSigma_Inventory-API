<?php

use App\Enums\StockTransactionTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')
                ->constrained('setup_warehouses', 'id', 'wst_warehouse')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->enum('type', StockTransactionTypes::toArray())
                ->comment('Type of transaction: in for stock added, out for stock removed');
            $table->foreignId('item_id')
                ->constrained('item_profile', 'id', 'wst_item')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->decimal('quantity', 10, 2)
                ->comment('Quantity of the item in the transaction');
            $table->foreignId('uom_id')
                ->constrained('setup_uom', 'id', 'wst_uom')
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Unit of measure for the item in the transaction');
            $table->json('uom_conversion')
                ->nullable()
                ->comment('Custom conversion factor for allowing cross UOM group conversions.');
            $table->foreignId('parent_item_id')
                ->nullable()
                ->constrained('warehouse_stock_transactions', 'id', 'wst_parent_item')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->nullableMorphs('referenceable', "wst_reference");// Polymorphic reference to the transaction source. Made Nullable so we can have transactions without a reference.
            $table->json('metadata')
                ->comment('Additional metadata for the transaction, such as reason, user, etc.');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_transactions');
    }
};
