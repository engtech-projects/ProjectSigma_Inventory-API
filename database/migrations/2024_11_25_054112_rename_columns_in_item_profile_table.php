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
        Schema::table('item_profile', function (Blueprint $table) {
            $table->renameColumn('thickness_val', 'thickness');
            $table->renameColumn('length_val', 'length');
            $table->renameColumn('width_val', 'width');
            $table->renameColumn('height_val', 'height');
            $table->renameColumn('outside_diameter_val', 'outside_diameter');
            $table->renameColumn('inside_diameter_val', 'inside_diameter');
            $table->renameColumn('volume_val', 'volume');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_profile', function (Blueprint $table) {
            $table->renameColumn('thickness', 'thickness_val');
            $table->renameColumn('length', 'length_val');
            $table->renameColumn('width', 'width_val');
            $table->renameColumn('height', 'height_val');
            $table->renameColumn('outside_diameter', 'outside_diameter_val');
            $table->renameColumn('inside_diameter', 'inside_diameter_val');
            $table->renameColumn('volume', 'volume_val');
        });
    }
};
