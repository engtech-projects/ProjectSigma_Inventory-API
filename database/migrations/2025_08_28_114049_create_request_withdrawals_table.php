<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\RequestStatuses;
use App\Enums\FuelWithdrawal;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_time');
            $table->foreignId('warehouse_id')->constrained('setup_warehouses')->restrictOnDelete()->cascadeOnUpdate();
            $table->nullableMorphs('chargeable');
            $table->string('equipment_no')->nullable();
            $table->string('smr')->nullable();
            $table->enum('fuel', FuelWithdrawal::toArray())->nullable();
            $table->string('reference_no')->unique();
            $table->json('metadata')->nullable();
            $table->json('approvals')->nullable();
            $table->enum('request_status', RequestStatuses::toArray());
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_withdrawals');
    }
};
