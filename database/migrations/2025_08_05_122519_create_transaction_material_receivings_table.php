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
        Schema::create('transaction_material_receivings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')
                ->constrained('setup_warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('reference_no')->unique();
            $table->foreignId('supplier_id')
                ->constrained('request_supplier')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('reference');
            $table->string('terms_of_payment');
            $table->string('particulars');
            $table->date('transaction_date');
            $table->foreignId('evaluated_by_id')
                ->constrained('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('transaction_material_receivings');
    }
};
