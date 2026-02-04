<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create new setup_departments table
        Schema::dropIfExists('setup_departments');
        Schema::create('setup_departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 75)->comment('Unique code for the department');
            $table->string('department_name', 255)->comment('Name of the department');
            $table->timestamps();
            $table->softDeletes();
        });
        // Migrate data from departments to setup_departments
        $departments = DB::table('departments')->select(
            'department_name',
            'created_at',
            'updated_at',
            'deleted_at'
        )->get();

        $insertData = [];
        foreach ($departments as $department) {
            $insertData[] = [
                // You may need to set a default or generate a code value here
                'code' => '', // Set appropriate code value if needed
                'department_name' => $department->department_name,
                'created_at' => $department->created_at,
                'updated_at' => $department->updated_at,
                'deleted_at' => $department->deleted_at,
            ];
        }
        if (!empty($insertData)) {
            DB::table('setup_departments')->insert($insertData);
        }
        // Drop the old departments table
        Schema::dropIfExists('departments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the departments table
        Schema::dropIfExists('departments');
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('hrms_id')->nullable()->comment('HRMS ID for the department');
            $table->string('department_name', 255)->comment('Name of the department');
            $table->timestamps();
            $table->softDeletes();
        });
        // Restore data from setup_departments
        $departments = DB::table('setup_departments')->select(
            'id',
            'department_name',
            'created_at',
            'updated_at',
            'deleted_at'
        )->get();
        $insertData = [];
        foreach ($departments as $department) {
            $insertData[] = [
                'hrms_id' => $department->id,
                'department_name' => $department->department_name,
                'created_at' => $department->created_at,
                'updated_at' => $department->updated_at,
                'deleted_at' => $department->deleted_at,
            ];
        }
        if (!empty($insertData)) {
            DB::table('departments')->insert($insertData);
        }
        // Drop the setup_departments table
        Schema::dropIfExists('setup_departments');
    }
};
