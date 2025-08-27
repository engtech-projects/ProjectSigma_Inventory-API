<?php

namespace App\Http\Requests;

use App\Enums\PurchaseOrderProcessingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseProcessingStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'processing_status' => [
                'required',
                'string',
                Rule::in(array_column(PurchaseOrderProcessingStatus::cases(), 'value')),
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $po = $this->route('requestPurchaseOrder');
            $newStatus = PurchaseOrderProcessingStatus::from($this->input('processing_status'));
            if ($po->isServed()) {
                $validator->errors()->add('processing_status', 'No further transactions allowed. This PO has already been served.');
                return;
            }
            if ($po->processing_status === $newStatus) {
                $validator->errors()->add('processing_status', 'Status is currently set to ' . $newStatus->value);
            } elseif (!$po->canTransitionTo) {
                $validator->errors()->add('processing_status', 'Invalid status transition. Valid next states are: ' . implode(', ', array_map(fn($s) => PurchaseOrderProcessingStatus::from($s)->value, $po->getValidNextStatuses())));
            }
        });
    }
}
