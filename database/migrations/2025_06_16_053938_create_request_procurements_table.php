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
        Schema::create('request_procurement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_requisition_slip_id')
                  ->constrained('request_stocks')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->enum('serve_status', ['served', 'unserved'])->default('unserved');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_procurements');
    }
};
