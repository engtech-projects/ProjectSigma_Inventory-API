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
        Schema::table('request_supplier', function (Blueprint $table) {
            $table->dropUnique('request_supplier_company_email_unique');
            $table->dropUnique('request_supplier_tin_unique');
            $table->string('company_contact_number', 50)->nullable()->change();
            $table->string('contact_person_designation')->nullable()->change();
            $table->string('tin')->nullable()->change();
            $table->enum('type_of_ownership', ['Single Proprietorship', 'Partnership', 'Corporation'])->nullable()->change();
            $table->dropColumn(['filled_by', 'filled_designation', 'filled_date', 'requirements_complete']);
            $table->json('metadata')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_supplier', function (Blueprint $table) {
            $table->string('company_email')->unique()->change();
            $table->string('company_contact_number', 50)->change();
            $table->string('contact_person_designation')->change();
            $table->string('tin')->unique()->change();
            $table->enum('type_of_ownership', ['Single Proprietorship', 'Partnership', 'Corporation'])->change();
            $table->enum('requirements_complete', ['Yes', 'No'])->after("terms_and_conditions");
            $table->string('filled_date')->after("terms_and_conditions");
            $table->string('filled_designation')->after("terms_and_conditions");
            $table->string('filled_by')->after("terms_and_conditions");
            $table->dropColumn(['metadata']);
        });
    }
};
