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
        Schema::table('request_stocks', function (Blueprint $table) {
            $table->dropForeign(['office_project']);
            $table->dropColumn(['office_project']);
            $table->unsignedBigInteger('section_id')->after('warehouse_id');
            $table->string('section_type')->after('section_id');
            $table->string('section_address')->nullable()->after('section_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_stocks', function (Blueprint $table) {
            $table->dropColumn(['section_id', 'section_type', 'section_address']);
            $table->unsignedBigInteger('office_project')->nullable();
            $table->foreign('office_project')->references('id')->on('projects');
        });
    }
};
