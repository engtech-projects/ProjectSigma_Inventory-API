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
        Schema::create('warehouse_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('item_profile')->onDelete('cascade');
            $table->foreignId('warehouse_transaction_id')->constrained('warehouse_transactions')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('warehouse_transaction_items')->onDelete('cascade');
            $table->float('quantity');
            $table->foreignId('uom')->constrained('setup_uom')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_transaction_items');
    }
};
