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
        Schema::create('request_supplier', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->unique();
            $table->string('company_name');
            $table->string('company_address');
            $table->string('company_contact_number');
            $table->string('company_email')->unique();
            $table->string('contact_person_name');
            $table->string('contact_person_number');
            $table->string('contact_person_designation');
            $table->enum('type_of_ownership', ['Single Proprietorship', 'Partnership', 'Corporation']);
            $table->string('nature_of_business');
            $table->string('products_services');
            $table->string('classification');
            $table->string('tin')->unique();
            $table->text('terms_and_conditions');
            $table->string('filled_by');
            $table->string('filled_designation');
            $table->string('filled_date');
            $table->enum('requirements_complete', ['Yes', 'No']);
            $table->text('remarks')->nullable();
            $table->string('created_by');
            $table->json('approvals');
            $table->string('request_status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_supplier');
    }
};
