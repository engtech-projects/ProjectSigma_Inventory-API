<?php

namespace App\Http\Controllers;

use App\Enums\RequestApprovalStatus;
use App\Enums\TransactionTypes;
use App\Models\RequestSupplier;
use App\Models\WarehouseTransaction;
use App\Models\WarehouseTransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class MRRController extends Controller
{
    public function index()
    {
        $mrrs = WarehouseTransaction::with(['items.item', 'items.supplier', 'warehouse', 'requestStock'])
            ->where('transaction_type', TransactionTypes::RECEIVING)
            ->whereJsonContains('metadata->is_petty_cash', true)
            ->paginate(20);

        return new JsonResponse([
            "success" => true,
            "message" => "MRR Successfully Fetched.",
            "data" => $mrrs,
        ], JsonResponse::HTTP_OK);
    }

    public function show($id)
    {
        $mrr = WarehouseTransaction::with([
            'items.item',
            'items.supplier',
            'warehouse',
            'requestStock.section'
        ])
        ->findOrFail($id);

        return response()->json($mrr);
    }

    public function update(Request $request, $id)
    {
        $mrr = WarehouseTransaction::findOrFail($id);
        
        // Validate that user can edit this MRR
        if ($mrr->request_status !== RequestApprovalStatus::PENDING) {
            return response()->json(['error' => 'MRR cannot be edited after approval'], 422);
        }

        $validated = $request->validate([
            'metadata.supplier_id' => 'nullable|exists:suppliers,id',
            'metadata.terms_of_payment' => 'nullable|string',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:warehouse_transaction_items,id',
            'items.*.actual_brand_purchase' => 'nullable|string',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.supplier_id' => 'nullable|exists:suppliers,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.accepted' => 'boolean',
            'items.*.rejected' => 'boolean',
            'items.*.remarks' => 'nullable|string',
        ]);

        // Update MRR metadata
        $metadata = $mrr->metadata;
        if (isset($validated['metadata']['supplier_id'])) {
            $metadata['supplier_id'] = $validated['metadata']['supplier_id'];
        }
        if (isset($validated['metadata']['terms_of_payment'])) {
            $metadata['terms_of_payment'] = $validated['metadata']['terms_of_payment'];
        }
        $mrr->metadata = $metadata;
        $mrr->save();

        // Update MRR items
        foreach ($validated['items'] as $itemData) {
            $item = WarehouseTransactionItem::findOrFail($itemData['id']);
            $item->update([
                'actual_brand_purchase' => $itemData['actual_brand_purchase'],
                'unit_price' => $itemData['unit_price'],
                'supplier_id' => $itemData['supplier_id'],
                'quantity_received' => $itemData['quantity_received'],
                'accepted' => $itemData['accepted'] ?? false,
                'rejected' => $itemData['rejected'] ?? false,
                'remarks' => $itemData['remarks'],
            ]);
        }

        return response()->json([
            'message' => 'MRR updated successfully',
            'mrr' => $mrr->load('items.item', 'items.supplier')
        ]);
    }

    public function getSuppliers()
    {
        $suppliers = RequestSupplier::select('id', 'name', 'code')->get();
        return response()->json($suppliers);
    }
}
