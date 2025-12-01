<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // Drop columns individually (MySQL 5.7 safer)
        Schema::table('request_turnovers', function (Blueprint $table) {
            if (Schema::hasColumn('request_turnovers', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('request_turnovers', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });

        // Rename requested_by → created_by
        DB::statement(
            "ALTER TABLE request_turnovers
             CHANGE COLUMN `requested_by` `created_by` BIGINT UNSIGNED NOT NULL"
        );

        // Add new fields
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->json('approvals')->nullable()->after('created_by');
            $table->string('request_status')->nullable()->after('approvals');
        });
    }

    public function down(): void
    {
        Schema::table('request_turnovers', function (Blueprint $table) {
            if (Schema::hasColumn('request_turnovers', 'approvals')) {
                $table->dropColumn('approvals');
            }
            if (Schema::hasColumn('request_turnovers', 'request_status')) {
                $table->dropColumn('request_status');
            }
        });

        // Rename created_by → requested_by
        DB::statement(
            "ALTER TABLE request_turnovers
             CHANGE COLUMN `created_by` `requested_by` BIGINT UNSIGNED NOT NULL"
        );

        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->bigInteger('approved_by')->unsigned()->nullable()->after('requested_by');
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])
                ->default('Pending')
                ->after('approved_by');
        });
    }
};
