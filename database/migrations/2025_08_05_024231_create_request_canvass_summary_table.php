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
        Schema::create('request_canvass_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_quotation_id')->constrained()->onDeleteRestrict()->onUpdateRestrict()->cascadeOnUpdate();
            $table->json('metadata')->nullable();
            $table->json('approvals');
            $table->string('request_status');
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_canvass_summary');
    }
};
