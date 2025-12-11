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
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->dateTime('transaction_date')->nullable()->after('transaction_type');
            $table->json('metadata')->after('transaction_date')->nullable();
            $table->string('reference_no')->after('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_date', 'metadata', 'reference_no']);
        });
    }
};
