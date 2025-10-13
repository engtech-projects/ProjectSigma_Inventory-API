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
        Schema::table('request_ncpo_items', function (Blueprint $table) {
            $table->dropColumn('new_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_ncpo_items', function (Blueprint $table) {
            $table->float('new_total')->nullable();
        });
    }
};
