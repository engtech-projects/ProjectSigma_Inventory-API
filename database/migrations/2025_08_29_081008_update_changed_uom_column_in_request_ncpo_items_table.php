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
        Schema::table('request_ncpo_items', function (Blueprint $table) {
            $table->dropColumn('changed_uom');
            $table->foreignId('changed_uom_id')
                  ->after('changed_qty')
                  ->nullable()
                  ->constrained('setup_uom')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_ncpo_items', function (Blueprint $table) {
            $table->dropForeign(['changed_uom_id']);
            $table->dropColumn('changed_uom_id');
            $table->string('changed_uom')->nullable();
        });
    }

};
