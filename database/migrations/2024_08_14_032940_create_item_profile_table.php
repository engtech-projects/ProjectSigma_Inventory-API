<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_profile', function (Blueprint $table) {
            $table->id();
            $table->string("sku");
            $table->string("item_description");
            $table->float('thickness_val');
            $table->foreignId('thickness_uom')->constrained('setup_uom');
            $table->float('length_val');
            $table->foreignId('length_uom')->constrained('setup_uom');
            $table->float('width_val');
            $table->foreignId('width_uom')->constrained('setup_uom');
            $table->float('height_val');
            $table->foreignId('height_uom')->constrained('setup_uom');
            $table->float('outside_diameter_val');
            $table->foreignId('outside_diameter_uom')->constrained('setup_uom');
            $table->float('inside_diameter_val');
            $table->foreignId('inside_diameter_uom')->constrained('setup_uom');
            $table->string("specification");
            $table->string("grade");
            $table->string("color");
            $table->foreignId('uom')->constrained('setup_uom');
            $table->foreignId('uom_group_id')->constrained('setup_uom_group');
            $table->float('uom_conversion_value');
            $table->enum('inventory_type',['Inventoriable', 'Non-Inventoriable']);
            $table->enum('active_status',['Active', 'Inactive']);
            $table->boolean('is_approved');
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
