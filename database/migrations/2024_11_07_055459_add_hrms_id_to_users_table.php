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
        Schema::table('users', function (Blueprint $table) {
            $table->string('hrms_id')->after('id');
            $table->string('employee_id')->after('hrms_id');
            $table->string('type')->after('remember_token');
            $table->string('accessibilities')->after('type');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('accessibilities');
            $table->dropColumn('type');
            $table->dropColumn('employee_id');
            $table->dropColumn('hrms_id');
        });
    }
};
