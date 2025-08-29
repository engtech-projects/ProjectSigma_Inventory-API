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
        Schema::create('request_ncpo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_ncpo_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('price_quotation_items')->onDelete('cascade');
            $table->foreignId('changed_supplier_id')->nullable()->constrained('request_supplier')->onDelete('set null');
            $table->text('changed_item_description')->nullable();
            $table->text('changed_specification')->nullable();
            $table->decimal('changed_qty', 10, 2)->nullable();
            $table->string('changed_uom')->nullable();
            $table->decimal('changed_unit_price', 10, 2)->nullable();
            $table->string('changed_brand')->nullable();
            $table->decimal('new_total', 10, 2)->nullable();
            $table->boolean('cancel_item')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_ncpo_items');
    }
};
