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
        Schema::table('users', function (Blueprint $table) {
            $table->string('accessibilities')->nullable()->after('type')->change();
        });
        DB::table('users')->update(['accessibilities' => null]);
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('employee_id')->nullable()->after('id');
            $table->dropColumn("hrms_id");
            $table->json('accessibilities')->nullable()->after('type')->change();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('employee_id');
            $table->unsignedInteger('hrms_id')->nullable()->after('id');
            $table->string('accessibilities')->nullable()->after('type')->change();
            $table->dropSoftDeletes();
        });
        DB::table('users')->update(['hrms_id' => DB::raw('id')]);
    }
};
