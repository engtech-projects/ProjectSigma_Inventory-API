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
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approval_status']);
        });

        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->renameColumn('requested_by', 'created_by');
        });

        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->json('approvals')->nullable()->after('created_by');
            $table->string('request_status')->nullable()->after('approvals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->dropColumn(['approvals', 'request_status']);
        });

        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->renameColumn('created_by', 'requested_by');
        });

        Schema::table('request_turnovers', function (Blueprint $table) {
            $table->string('approved_by')->nullable()->after('requested_by');
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])
                  ->default('Pending')
                  ->after('approved_by');
        });
    }
};
