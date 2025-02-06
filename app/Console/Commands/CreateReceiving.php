<?php

namespace App\Console\Commands;

use App\Models\MaterialsReceiving;
use App\Models\MaterialsReceivingItem; // Import the model for items
use Illuminate\Console\Command;

class CreateReceiving extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-receiving';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to create a materials receiving entry for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create Materials Receiving Entry
        $materialsReceiving = MaterialsReceiving::create([
            'warehouse_id' => 1,
            'reference_no' => 'REF1',
            'supplier_id' => 2,
            'reference_code' => 'RC1',
            'terms_of_payment' => 'Monthly',
            'transaction_date' => now(),
            'project_id' => 3,
            'equipment_no' => 'EQ1',
            'total_net_of_vat_cost' => 1000.00,
            'total_input_vat' => 120.00,
            'grand_total' => 1120.00
        ]);

        // Create related items
        $items = [
            [
                'item_code' => 'ITEM001',
                'item_profile_id' => 1,
                'specification' => 'Spec A',
                'actual_brand' => 'Brand A',
                'qty' => 10,
                'uom_id' => 1,
                'unit_price' => 100,
                'ext_price' => 1000,
                'status' => 'Received',
                'remarks' => 'First item'
            ],
            [
                'item_code' => 'ITEM002',
                'item_profile_id' => 2,
                'specification' => 'Spec B',
                'actual_brand' => 'Brand B',
                'qty' => 20,
                'uom_id' => 2,
                'unit_price' => 50,
                'ext_price' => 1000,
                'status' => 'Received',
                'remarks' => 'Second item'
            ]
        ];

        foreach ($items as $itemData) {
            $itemData['materials_receiving_id'] = $materialsReceiving->id;
            MaterialsReceivingItem::create($itemData);
        }

        $this->info('Materials Receiving entry and items created successfully:');
        $this->info($materialsReceiving);
    }
}
