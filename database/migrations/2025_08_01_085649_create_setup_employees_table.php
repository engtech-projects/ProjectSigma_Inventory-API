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
        Schema::rename('employees', 'setup_employees');
        Schema::table('setup_employees', function (Blueprint $table) {
            $table->dropColumn('hrms_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('setup_employees', 'employees');
        Schema::table('employees', function (Blueprint $table) {
            $table->string('hrms_id')->nullable()->after('id');
        });
        DB::table('employees')->update(['hrms_id' => DB::raw('id')]);
    }
};
