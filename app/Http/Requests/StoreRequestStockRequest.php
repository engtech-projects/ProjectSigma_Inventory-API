<?php

namespace App\Http\Requests;

use App\Enums\AssignTypes;
use App\Enums\RSRemarksEnums;
use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreRequestStockRequest extends FormRequest
{
    use HasApprovalValidation;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (gettype($this->items) == "string") {
            $this->merge([
                "items" => json_decode($this->items, true)
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'request_for' => 'required|string|max:255',
            'warehouse_id' => 'required|numeric|exists:warehouse,id',
            'section_id' => 'required|integer',
            'section_type' => [
                'nullable',
                'string',
                new Enum(AssignTypes::class),
            ],
            'office_project_address' => 'required|string|max:255',
            'date_prepared' => 'required|date',
            'date_needed' => 'required|date',
            'equipment_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_stocks', 'equipment_no')
                    ->where(fn ($q) => $q->where('equipment_no', '!=', 'N/A')),
            ],
            'remarks' => ['nullable', 'string', new Enum(RSRemarksEnums::class)],
            // Updated type_of_request validation
            'type_of_request' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $allowedTypes = [
                        'Recommended Request',
                        'Special Case of Request',
                        'N/A'
                    ];

                    $months = [
                        'January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];

                    // Check if it's one of the simple allowed types
                    if (in_array($value, $allowedTypes)) {
                        return;
                    }

                    // Check if it's a consolidated request with month
                    $consolidatedPrefix = 'Consolidated Request for the month of ';
                    if (str_starts_with($value, $consolidatedPrefix)) {
                        $month = substr($value, strlen($consolidatedPrefix));
                        if (in_array($month, $months)) {
                            return;
                        }
                    }

                    $fail('The selected type of request is invalid.');
                }
            ],
            'month' => [
                'nullable',
                'string',
                Rule::in([
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ]),
                // Remove this validation since it's handled in frontend
                // 'required_if:type_of_request,Consolidated Request for the month of'
            ],
            'is_approved' => 'boolean',
            'contact_no' => 'nullable|integer',
            'current_smr' => 'nullable|string|max:255',
            'previous_smr' => 'nullable|string|max:255',
            'unused_smr' => 'nullable|string|max:255',
            'next_smr' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|integer|exists:setup_uom,id',
            'items.*.item_id' => 'required|exists:item_profile,id',
            'items.*.specification' => 'nullable|string',
            'items.*.preferred_brand' => 'nullable|string',
            'items.*.reason' => 'nullable|string',
            ...$this->storeApprovals(),
        ];
    }

    public function messages()
    {
        return [
            'date_needed.required' => 'The date when the stock is needed is required.',
            'items.required' => 'At least one item must be specified.',
            'items.*.item_id.exists' => 'The selected item does not exist.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
            'reference_no.unique' => 'The reference number has already been taken.',
            'equipment_no.unique' => 'The equipment number has already been taken.',
            'month.required_if' => 'The month field is required when type of request is "Consolidated Request for the month of".',
        ];
    }
}
