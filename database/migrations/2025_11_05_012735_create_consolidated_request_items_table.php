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
        Schema::create('consolidated_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consolidated_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('requisition_slip_item_id')->constrained('request_requisition_slip_items')->onDelete('cascade');
            $table->foreignId('requisition_slip_id')->constrained('request_requisition_slips')->onDelete('cascade');
            $table->decimal('quantity_consolidated', 10, 2);
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consolidated_request_items');
    }
};
