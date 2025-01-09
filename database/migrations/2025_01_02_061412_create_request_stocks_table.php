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
        Schema::create('request_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouse')->onDelete('cascade');
            $table->string('request_for');
            $table->string('requestor');
            $table->string('requestor_address');
            $table->string('delivered_to');
            $table->date('date_prepared');
            $table->date('date_needed');
            $table->string('equipment_no')->unique();
            $table->json('approvals');
            $table->string('created_by');
            $table->string('request_status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_stocks');
    }
};
