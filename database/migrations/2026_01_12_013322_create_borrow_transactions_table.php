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
        Schema::create('borrow_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->date('date_time_borrowed');
            $table->string('borrowed_by');
            $table->string('borrowed_contact_no')->nullable();
            $table->string('returned_by')->nullable();
            $table->date('date_time_returned')->nullable();
            $table->string('received_by')->nullable();
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable();
            $table->json('approvals')->nullable();
            $table->enum('request_status', RequestStatuses::toArray())->default('pending');
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_transactions');
    }
};
