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
        Schema::create('price_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_quotation_id')
                  ->constrained('price_quotations')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->unsignedBigInteger('item_id');
            $table->string('actual_brand');
            $table->decimal('unit_price', 12, 2);
            $table->text('remarks_during_canvass')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_quotation_items');
    }
};
