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
        Schema::create('request_turnovers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->date('date');
            $table->foreignId('from_warehouse_id')
            ->constrained('setup_warehouses')
            ->onDelete('restrict')
            ->onUpdate('cascade');
            $table->foreignId('to_warehouse_id')
            ->constrained('setup_warehouses')
            ->onDelete('restrict')
            ->onUpdate('cascade');
            $table->string('requested_by');
            $table->string('approved_by')->nullable();
            $table->date('received_date')->nullable();
            $table->string('received_name')->nullable();
            $table->enum('approval_status', [
                'Pending',
                'Approved',
                'Rejected'
            ])->default('Pending');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('request_turnovers');
    }
};
