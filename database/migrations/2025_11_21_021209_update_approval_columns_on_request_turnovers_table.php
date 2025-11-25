<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approval_status']);
        });
        DB::statement('ALTER TABLE request_turnovers CHANGE `requested_by` `created_by` bigint unsigned NOT NULL');
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->json('approvals')->nullable()->after('created_by');
            $table->string('request_status')->nullable()->after('approvals');
        });
    }

    public function down(): void
    {
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->dropColumn(['approvals', 'request_status']);
        });
        DB::statement('ALTER TABLE request_turnovers CHANGE `created_by` `requested_by` bigint unsigned NOT NULL');
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->bigInteger('approved_by')->unsigned()->nullable()->after('requested_by');
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])
                  ->default('Pending')
                  ->after('approved_by');
        });
    }
};
