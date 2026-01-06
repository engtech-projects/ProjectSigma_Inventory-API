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
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->dropForeign(['from_warehouse_id']);
            $table->dropForeign(['to_warehouse_id']);
            $table->dropColumn(['from_warehouse_id', 'to_warehouse_id']);

            $table->string('from_type')->nullable()->after('id');
            $table->unsignedBigInteger('from_id')->nullable()->after('from_type');
            $table->string('to_type')->nullable()->after('from_id');
            $table->unsignedBigInteger('to_id')->nullable()->after('to_type');

            $table->index(['from_type', 'from_id']);
            $table->index(['to_type', 'to_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->dropIndex(['from_type', 'from_id']);
            $table->dropIndex(['to_type', 'to_id']);
            $table->dropColumn(['from_type', 'from_id', 'to_type', 'to_id']);

            $table->foreignId('from_warehouse_id')->nullable()->after('id')
                  ->constrained('setup_warehouses')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('to_warehouse_id')->nullable()->after('from_warehouse_id')
                  ->constrained('setup_warehouses')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }
};
