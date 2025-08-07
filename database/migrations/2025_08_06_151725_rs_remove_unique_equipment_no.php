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
        Schema::table('request_requisition_slips', function (Blueprint $table) {
            $table->dropUnique('request_stocks_equipment_no_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_requisition_slips', function (Blueprint $table) {
            $table->unique('equipment_no', 'request_stocks_equipment_no_unique');
        });
    }
};
