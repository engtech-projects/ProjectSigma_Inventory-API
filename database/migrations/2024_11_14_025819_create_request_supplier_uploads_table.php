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
        Schema::create('request_supplier_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_supplier_id')->constrained('request_supplier');
            $table->string('attachment_name');
            $table->string('file_location');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_supplier_uploads');
    }
};
