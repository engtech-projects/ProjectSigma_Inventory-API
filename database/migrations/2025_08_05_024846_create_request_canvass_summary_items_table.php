<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_canvass_summary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_canvass_summary_id')->constrained()->onDeleteRestrict()->onUpdateRestrict();
            $table->foreignId('price_quotation_item_id')->constrained()->onDeleteRestrict()->onUpdateRestrict();
            $table->decimal('unit_price', 12, 2);
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
        Schema::dropIfExists('request_canvass_summary_items');
    }
};
