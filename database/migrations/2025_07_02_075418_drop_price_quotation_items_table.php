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
        Schema::dropIfExists('price_quotation_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('price_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->decimal('price', 12, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }
};
