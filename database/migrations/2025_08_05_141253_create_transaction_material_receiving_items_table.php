<?php

use App\Enums\ServeStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_material_receiving_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_material_receiving_id')
                ->constrained('transaction_material_receivings', 'id', 'tmri_tmrid')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('item_id')
                ->constrained('item_profile', 'id', 'tmri_itemid')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('specification')->nullable();
            $table->string('actual_brand_purchase')->nullable();
            $table->decimal('requested_quantity', 10, 2);
            $table->decimal('quantity', 10, 2);
            $table->foreignId('uom_id')
                ->constrained('setup_uom', 'id', 'tmri_uomid')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->decimal('unit_price', 10, 2);
            $table->enum('serve_status', ServeStatus::toArray());
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_material_receiving_items');
    }
};
