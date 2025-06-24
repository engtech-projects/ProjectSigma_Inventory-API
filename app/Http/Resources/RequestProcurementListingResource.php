<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestProcurementListingResource extends JsonResource
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
            "rs_reference_no" => $this->requestStock->reference_no ?? "",
            "rs_date_prepared_human" => $this->requestStock->date_prepared_human ?? "",
            "rs_date_needed_human" => $this->requestStock->date_needed_human ?? "",
            "status" => $this->requestStock->request_status ?? "",
        ];
    }
}
