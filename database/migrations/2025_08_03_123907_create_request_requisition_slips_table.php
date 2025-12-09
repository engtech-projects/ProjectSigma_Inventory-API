<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('request_stocks', 'request_requisition_slips');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('request_requisition_slips', 'request_stocks');
    }
};
