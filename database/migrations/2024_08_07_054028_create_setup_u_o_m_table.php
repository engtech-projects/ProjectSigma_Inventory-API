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
        Schema::create('setup_uom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('setup_uom_group');
            $table->string('name');
            $table->string('symbol');
            $table->decimal('conversion', 15, 4)->nullable();
            $table->boolean('is_standard');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setup_uom');
    }
};
