<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SyncSuppliersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_code' => $this->supplier_code,
            'company_name' => $this->company_name,
            'contact_person_name' => $this->contact_person_name
        ];
    }
}
