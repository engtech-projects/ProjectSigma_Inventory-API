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
        Schema::table('item_profile', function (Blueprint $table) {
            $table->dropForeign(['thickness_uom']);
            $table->dropForeign(['length_uom']);
            $table->dropForeign(['width_uom']);
            $table->dropForeign(['height_uom']);
            $table->dropForeign(['outside_diameter_uom']);
            $table->dropForeign(['inside_diameter_uom']);
            $table->dropForeign(['volume_uom']);
            $table->dropForeign(['uom_conversion_group_id']);
            $table->dropColumn(['thickness_uom', 'length_uom', 'width_uom', 'height_uom', 'outside_diameter_uom', 'inside_diameter_uom', 'volume_uom', 'uom_conversion_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_profile', function (Blueprint $table) {
            $table->foreignId('thickness_uom')->nullable()->constrained('setup_uom');
            $table->foreignId('length_uom')->nullable()->constrained('setup_uom');
            $table->foreignId('width_uom')->nullable()->constrained('setup_uom');
            $table->foreignId('height_uom')->nullable()->constrained('setup_uom');
            $table->foreignId('outside_diameter_uom')->nullable()->constrained('setup_uom');
            $table->foreignId('inside_diameter_uom')->nullable()->constrained('setup_uom');
            $table->foreignId('volume_uom')->nullable()->constrained('setup_uom');
            $table->foreignId('uom_conversion_group_id')->nullable()->constrained('setup_uom_group');
        });
    }
};
