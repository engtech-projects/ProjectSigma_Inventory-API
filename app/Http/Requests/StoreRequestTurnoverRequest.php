<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\HasApprovalValidation;

class StoreRequestTurnoverRequest extends FormRequest
{
    use HasApprovalValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],

            'from_type' => ['required', Rule::in($this->allowedTypes())],
            'from_id' => [
                'bail',
                'required',
                function ($attr, $value, $fail) {
                    if (!$this->entityExists('from')) {
                        $fail('The selected source is invalid.');
                    }
                },
            ],

            'to_type' => ['required', Rule::in($this->allowedTypes())],
            'to_id' => [
                'bail',
                'required',
                function ($attr, $value, $fail) {
                    if (!$this->entityExists('to')) {
                        $fail('The selected destination is invalid.');
                    }

                    if (
                        $this->input('from_type') === $this->input('to_type') &&
                        $this->input('from_id') == $value
                    ) {
                        $fail('Source and destination must be different.');
                    }
                },
            ],

            'metadata' => ['nullable', 'array'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'bail',
                'required',
                'distinct',
                Rule::exists('item_profile', 'id'),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.uom' => ['required', Rule::exists('setup_uom', 'id')],
            'items.*.condition' => ['nullable', 'string', 'max:100'],
            'items.*.remarks' => ['nullable', 'string', 'max:500'],

            ...$this->storeApprovals(),
        ];
    }

    /**
     * Validate entity existence based on type.
     */
    protected function entityExists(string $direction): bool
    {
        $type = $this->input("{$direction}_type");
        $id   = $this->input("{$direction}_id");

        $table = match ($type) {
            'Department' => 'setup_departments',
            'Project'    => 'setup_projects',
            'Employee'   => 'setup_employee',
            'Warehouse'  => 'setup_warehouses',
            default      => null,
        };

        return $table
            ? DB::table($table)->where('id', $id)->exists()
            : false;
    }

    protected function allowedTypes(): array
    {
        return ['Department', 'Project', 'Employee', 'Warehouse'];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item is required.',
            'items.*.item_id.distinct' => 'Duplicate items are not allowed.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.gt' => 'Quantity must be greater than zero.',
            'items.*.uom.required' => 'Unit of measure is required.',
        ];
    }
}
