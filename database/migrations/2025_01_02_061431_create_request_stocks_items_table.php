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
        Schema::create('request_stocks_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_stock_id')->constrained()->onDelete('cascade');
            $table->float('quantity');
            $table->string('unit');
            $table->foreignId('item_id')->constrained('item_profile')->onDelete('cascade');
            $table->string('specification')->nullable();
            $table->string('preferred_brand')->nullable();
            $table->text('reason')->nullable();
            $table->string('location')->nullable();
            $table->float('location_qty')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->string('type_of_request')->nullable();
            $table->integer('contact_no')->nullable();
            $table->string('remarks')->nullable();
            $table->string('current_smr')->nullable();
            $table->string('previous_smr')->nullable();
            $table->string('unused_smr')->nullable();
            $table->string('next_smr')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_stocks_items');
    }
};
