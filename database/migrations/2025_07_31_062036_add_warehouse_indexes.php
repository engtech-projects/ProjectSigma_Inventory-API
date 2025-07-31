<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouse', function (Blueprint $table) {
            $table->unique(['owner_id', 'owner_type'], 'warehouse_owner_index');
            $table->index(['owner_id'], 'warehouse_ownerid_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse', function (Blueprint $table) {
            $table->dropIndex('warehouse_owner_index');
            $table->dropIndex('warehouse_ownerid_index');
        });
    }
};
