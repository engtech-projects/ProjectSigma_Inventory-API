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
        Schema::create('item_profile', function (Blueprint $table) {
            $table->id();
            $table->string("sku");
            $table->string("item_description");
            $table->float('thickness_val')->nullable();
            $table->foreignId('thickness_uom')->nullable()->constrained('setup_uom');
            $table->float('length_val')->nullable();
            $table->foreignId('length_uom')->nullable()->constrained('setup_uom');
            $table->float('width_val')->nullable();
            $table->foreignId('width_uom')->nullable()->constrained('setup_uom');
            $table->float('height_val')->nullable();
            $table->foreignId('height_uom')->nullable()->constrained('setup_uom');
            $table->float('outside_diameter_val')->nullable();
            $table->foreignId('outside_diameter_uom')->nullable()->constrained('setup_uom');
            $table->float('inside_diameter_val')->nullable();
            $table->foreignId('inside_diameter_uom')->nullable()->constrained('setup_uom');
            $table->string("specification")->nullable();
            $table->float('volume_val')->nullable();
            $table->foreignId('volume_uom')->nullable()->constrained('setup_uom');
            $table->string("grade")->nullable();
            $table->string("color")->nullable();
            $table->foreignId('uom')->constrained('setup_uom');
            $table->foreignId('uom_conversion_group_id')->nullable()->constrained('setup_uom_group');
            $table->float('uom_conversion_value')->nullable();
            $table->string("item_group");
            $table->string("sub_item_group");
            $table->enum('inventory_type', ['Inventoriable', 'Non-Inventoriable']);
            $table->enum('active_status', ['Active', 'Inactive']);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_profile');
    }
};
