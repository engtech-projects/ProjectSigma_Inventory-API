<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('warehouse')->upsert(
            [
                [
                    'id' => 1,
                    'name' => 'Main',
                    'location' => null,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ],
            [ "id" ],
            [ "name", "location", "updated_at", "deleted_at"]
        );
    }
}
