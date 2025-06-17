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
        Schema::dropIfExists('materials_receiving_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the table if rolling back
        Schema::create('materials_receiving_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materials_receiving_id')->constrained('materials_receiving')->onDelete('cascade');
            $table->string('item_code');
            $table->foreignId('item_profile_id')->constrained('item_profile')->onDelete('cascade');
            $table->string('specification');
            $table->string('actual_brand');
            $table->float('qty');
            $table->foreignId('uom_id')->constrained('setup_uom')->onDelete('cascade');
            $table->float('unit_price');
            $table->float('ext_price');
            $table->string('status');
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
