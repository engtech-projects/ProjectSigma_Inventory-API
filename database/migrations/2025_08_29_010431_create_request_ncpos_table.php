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
        Schema::create('request_ncpos', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('ncpo_no')->unique();
            $table->foreignId('po_id')->constrained('request_purchase_orders')->onDelete('cascade');
            $table->text('justification');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->json('approvals')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_ncpos');
    }
};
