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
        Schema::create('request_procurement_canvassers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_procurement_id')
                  ->constrained('request_procurement');
            $table->foreignId('user_id')
                  ->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_procurement_canvassers');
    }
};
