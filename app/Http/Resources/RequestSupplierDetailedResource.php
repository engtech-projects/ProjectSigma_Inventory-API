<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestSupplierDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "company_name" => $this->company_name,
            "company_address" => $this->company_address,
            "contact_person_name" => $this->contact_person_name,
            "contact_person_number" => $this->contact_person_number,
        ];
    }
}
