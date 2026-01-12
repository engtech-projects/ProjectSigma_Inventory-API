<?php

use App\Enums\RequestStatuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consolidated_requests', function (Blueprint $table) {
            $table->json('approvals')->nullable()->after('remarks');
            $table->enum('request_status', RequestStatuses::toArray())->nullable()->after('approvals');
        });
        DB::statement(
            "ALTER TABLE consolidated_requests
             CHANGE COLUMN `consolidated_by` `created_by` VARCHAR(255) NOT NULL AFTER `request_status`"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE consolidated_requests
             CHANGE COLUMN `created_by` `consolidated_by` VARCHAR(255) NOT NULL AFTER `purpose`"
        );
        Schema::table('consolidated_requests', function (Blueprint $table) {
            if (Schema::hasColumn('consolidated_requests', 'approvals')) {
                $table->dropColumn('approvals');
            }
            if (Schema::hasColumn('consolidated_requests', 'request_status')) {
                $table->dropColumn('request_status');
            }
        });
    }
};
