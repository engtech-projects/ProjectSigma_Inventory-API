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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hrms_id')->unique();
            $table->string("first_name");
            $table->string("middle_name")->nullable();
            $table->string("family_name");
            $table->string("name_suffix")->nullable();
            $table->string("nick_name")->nullable();
            $table->string("gender");
            $table->date("date_of_birth");
            $table->string("place_of_birth");
            $table->string("citizenship")->nullable();
            $table->string("blood_type")->nullable();
            $table->string("civil_status")->nullable();
            $table->string("date_of_marriage")->nullable();
            $table->string("telephone_number")->nullable();
            $table->string("mobile_number")->nullable();
            $table->string("email")->nullable();
            $table->string("religion")->nullable();
            $table->string("weight")->nullable();
            $table->string("height")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
