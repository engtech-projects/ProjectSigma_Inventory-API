<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** * Run the migrations. * * @return void */
    public function up()
    {
        Schema::table('item_profile', function (Blueprint $table) {
            $table->string('thickness')->nullable()->change();
            $table->string('length')->nullable()->change();
            $table->string('width')->nullable()->change();
            $table->string('height')->nullable()->change();
            $table->string('outside_diameter')->nullable()->change();
            $table->string('inside_diameter')->nullable()->change();
            $table->string('volume')->nullable()->change();
            $table->string('angle')->nullable()->after('inside_diameter');
            $table->string('size')->nullable()->after('angle');
            $table->string('weight')->nullable()->after('volume');
            $table->string('volts')->nullable()->after('grade');
            $table->string('plates')->nullable()->after('volts');
            $table->string('part_number')->nullable()->after('plates');
        });
    }
    /** * Reverse the migrations. * * @return void */
    public function down()
    {
        Schema::table('item_profile', function (Blueprint $table) {
            $table->float('thickness')->nullable()->change();
            $table->float('length')->nullable()->change();
            $table->float('width')->nullable()->change();
            $table->float('height')->nullable()->change();
            $table->float('outside_diameter')->nullable()->change();
            $table->float('inside_diameter')->nullable()->change();
            $table->float('volume')->nullable()->change();
            $table->dropColumn(['angle', 'size', 'weight', 'volts', 'plates', 'part_number']);
        });
    }
};
