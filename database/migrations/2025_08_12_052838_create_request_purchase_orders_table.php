<?php

use App\Enums\PurchaseOrderProcessingStatus;
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
        Schema::create('request_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('po_number')->unique();
            $table->foreignId('request_canvass_summary_id')
                ->constrained('request_canvass_summary')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('name_on_receipt')->nullable();
            $table->string('delivered_to')->nullable();
            $table->enum('processing_status', PurchaseOrderProcessingStatus::toArray())->nullable();
            $table->json('metadata')
                ->nullable()
                ->comment('Additional metadata for the request, such as VAT details per item, etc.');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->json('approvals')->nullable();
            $table->enum('request_status', RequestStatuses::toArray());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_purchase_orders');
    }
};
