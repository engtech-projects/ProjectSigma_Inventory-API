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
        Schema::create('request_turnover_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_turnover_id')->constrained('request_turnovers')
            ->onDelete('restrict')
            ->onUpdate('cascade');
            ;
            $table->foreignId('item_id')->constrained('item_profile')
            ->onDelete('restrict')
            ->onUpdate('cascade');
            ;
            $table->decimal('quantity', 15, 2);
            $table->foreignId('uom')->constrained('setup_uom')
            ->onDelete('restrict')
            ->onUpdate('cascade');
            ;
            $table->string('condition')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('accept_status', ['Pending', 'Accepted', 'Rejected'])->default('Pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_turnover_items');
    }
};
