<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasurement;
use App\Models\UOM;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SetupUomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('setup_uom')->upsert(
            [
                // Length
                [
                    'group_id' => 1, 'name' => 'Meter', 'symbol' => 'm', 'conversion' => 1.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 1, 'name' => 'Kilometer', 'symbol' => 'km', 'conversion' => 1000.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 1, 'name' => 'Centimeter', 'symbol' => 'cm', 'conversion' => 0.01, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 1, 'name' => 'Millimeter', 'symbol' => 'mm', 'conversion' => 0.001, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 1, 'name' => 'Foot', 'symbol' => 'ft', 'conversion' => 0.3048, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 1, 'name' => 'Inch', 'symbol' => 'in', 'conversion' => 0.0254, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 1, 'name' => 'Yard', 'symbol' => 'yd', 'conversion' => 0.9144, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],

                // Weight
                [
                    'group_id' => 2, 'name' => 'Kilogram', 'symbol' => 'kg', 'conversion' => 1.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 2, 'name' => 'Gram', 'symbol' => 'g', 'conversion' => 0.001, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 2, 'name' => 'Ton', 'symbol' => 't', 'conversion' => 1000.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 2, 'name' => 'Pound', 'symbol' => 'lb', 'conversion' => 0.453592, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 2, 'name' => 'Quintal', 'symbol' => 'q', 'conversion' => 100.0, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],

                // Volume
                [
                    'group_id' => 3, 'name' => 'Liter', 'symbol' => 'L', 'conversion' => 1.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 3, 'name' => 'Milliliter', 'symbol' => 'mL', 'conversion' => 0.001, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 3, 'name' => 'Gallon', 'symbol' => 'gal', 'conversion' => 3.78541, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 3, 'name' => 'Cubic Foot', 'symbol' => 'ft³', 'conversion' => 0.0283168, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],

                // Area
                [
                    'group_id' => 4, 'name' => 'Square Meter', 'symbol' => 'm²', 'conversion' => 1.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 4, 'name' => 'Square Kilometer', 'symbol' => 'km²', 'conversion' => 1000000.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 4, 'name' => 'Hectare', 'symbol' => 'ha', 'conversion' => 10000.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 4, 'name' => 'Square Foot', 'symbol' => 'ft²', 'conversion' => 0.092903, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 4, 'name' => 'Square Yard', 'symbol' => 'yd²', 'conversion' => 0.836127, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 4, 'name' => 'Square Inch', 'symbol' => 'in²', 'conversion' => 0.00064516, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],


                // Force
                [
                    'group_id' => 5, 'name' => 'Newton', 'symbol' => 'N', 'conversion' => 1.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 5, 'name' => 'Kilonewton', 'symbol' => 'kN', 'conversion' => 1000.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 5, 'name' => 'Pound-force', 'symbol' => 'lbf', 'conversion' => 4.44822, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],

                //Dimension
                [
                    'group_id' => 6, 'name' => 'Cubic Meter', 'symbol' => 'm³', 'conversion' => 1.0, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 6, 'name' => 'Cubic Centimeter', 'symbol' => 'cm³', 'conversion' => 1e-6, 'is_standard' => true,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 6, 'name' => 'Cubic Inch', 'symbol' => 'in³', 'conversion' => 0.0000163871, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'group_id' => 6, 'name' => 'Cubic Foot', 'symbol' => 'ft³', 'conversion' => 0.0283168, 'is_standard' => false,
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ],
            [ "group_id" ],
            [ "name", "symbol", "conversion", "updated_at", "deleted_at"]
        );
    }
}
