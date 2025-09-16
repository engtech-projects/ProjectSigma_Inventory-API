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
        Schema::rename('request_stocks_items', 'request_requisition_slip_items');
        Schema::table('request_requisition_slip_items', function (Blueprint $table) {
            $table->renameColumn('request_stock_id', 'request_requisition_slip_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('request_requisition_slip_items', 'request_stocks_items');
        Schema::table('request_stocks_items', function (Blueprint $table) {
            $table->renameColumn('request_requisition_slip_id', 'request_stock_id');
        });
    }
};
