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
        Schema::table('request_requisition_slip_items', function (Blueprint $table) {
            $table->foreignId('consolidated_request_id')->nullable()->after('id')->constrained('consolidated_requests')
            ->onDelete('restrict')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_requisition_slip_items', function (Blueprint $table) {
            $table->dropForeign(['consolidated_request_id']);
            $table->dropIndex(['consolidated_request_id']);
            $table->dropColumn('consolidated_request_id');
        });
    }
};
