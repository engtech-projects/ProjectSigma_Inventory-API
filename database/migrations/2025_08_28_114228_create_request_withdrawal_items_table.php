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
        Schema::create('request_withdrawal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_withdrawal_id')
                ->constrained('request_withdrawals')
                ->cascadeOnDelete();
            $table->foreignId('item_id')
                ->constrained('item_profile')
                ->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->foreignId('uom_id')
                ->constrained('setup_uom')
                ->cascadeOnDelete();
            $table->string('purpose_of_withdrawal')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_withdrawal_items');
    }
};
