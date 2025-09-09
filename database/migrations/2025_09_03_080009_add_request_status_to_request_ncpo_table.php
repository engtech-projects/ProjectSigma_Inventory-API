<?php

use App\Enums\RequestStatuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('request_ncpos', function (Blueprint $table) {
            $table->enum('request_status', RequestStatuses::toArray())->after('approvals')->default(RequestStatuses::PENDING);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_ncpos', function (Blueprint $table) {
            $table->dropColumn('request_status');
        });
    }
};
