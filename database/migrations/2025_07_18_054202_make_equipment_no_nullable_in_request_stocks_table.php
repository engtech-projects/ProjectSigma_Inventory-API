<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('request_stocks', function (Blueprint $table) {
            $table->string('equipment_no')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('request_stocks')
        ->whereNull('equipment_no')
        ->update(['equipment_no' => '']);
        Schema::table('request_stocks', function (Blueprint $table) {
            $table->string('equipment_no')->nullable(false)->change();
        });
    }
};
