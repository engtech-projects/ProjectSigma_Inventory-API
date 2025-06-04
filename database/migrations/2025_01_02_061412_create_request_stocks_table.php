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
        Schema::create('request_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('request_for');
            $table->foreignId('warehouse_id')->constrained('warehouse')->onDelete('cascade');
            $table->foreignId('office_project')->constrained('projects');
            $table->string('office_project_address');
            $table->date('date_prepared');
            $table->date('date_needed');
            $table->string('equipment_no')->unique();
            $table->string('type_of_request')->nullable();
            $table->integer('contact_no')->nullable();
            $table->string('remarks')->nullable();
            $table->string('current_smr')->nullable();
            $table->string('previous_smr')->nullable();
            $table->string('unused_smr')->nullable();
            $table->string('next_smr')->nullable();
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
