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
        Schema::dropIfExists('materials_receiving');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the table with its original structure
        Schema::create('materials_receiving', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->after('id');
            $table->enum('parent_type', ['receiving', 'transfer', 'withdrawal'])->after('parent_id');
            $table->foreignId('warehouse_id')->constrained('warehouse')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('request_supplier')->onDelete('cascade');
            $table->string('reference_no')->unique();
            $table->string('reference_code');
            $table->string('terms_of_payment');
            $table->string('particulars');
            $table->date('transaction_date');
            $table->foreignId('project_id')->constrained('projects');
            $table->string('equipment_no');
            $table->string('source_po');
            $table->float('total_net_of_vat_cost');
            $table->float('total_input_vat');
            $table->float('grand_total');
            $table->json('approvals');
            $table->string('created_by');
            $table->string('request_status');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};