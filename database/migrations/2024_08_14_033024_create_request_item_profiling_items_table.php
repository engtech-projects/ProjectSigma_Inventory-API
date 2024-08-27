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
        Schema::create('request_itemprofiling_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_itemprofiling_id')->constrained('request_itemprofiling');
            $table->foreignId('item_profile_id')->constrained('item_profile');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_item_profiling_items');
    }
};
