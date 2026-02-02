<?php

use App\Enums\RSRemarksEnums;
use App\Http\Services\MrrService;
use App\Models\RequestRequisitionSlip;
use App\Models\TransactionMaterialReceiving;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $approvedPettycashRs = RequestRequisitionSlip::isApproved()
        ->where('remarks', RSRemarksEnums::PETTYCASH->value)
        ->get();
        foreach ($approvedPettycashRs as $rs) {
            $mrrService = new MrrService(new TransactionMaterialReceiving());
            $mrrService->createPettyCashMrrFromRequestRequisitionSlip($rs);
        }
        Schema::dropIfExists('warehouse_transaction_items');
        Schema::dropIfExists('warehouse_transactions');
        // Skipped Received Requests, Good that the data isn't too big
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Too Complicated to reverse
    }
};
