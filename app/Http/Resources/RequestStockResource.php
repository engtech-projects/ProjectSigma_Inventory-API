<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestStockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return[
            ...parent::toArray($request)
        //     'id' => $this>id,
        //     'reference_no' => $this->reference_no,
        //     'request_status' => $this->request_status,
        //     'created_by' => $this->created_by,
        //     'created_at' => $this->created_at,
        //     'updated_at' => $this->updated_at,
        //     'project' => $this->project,
        //     'warehouse' => $this->warehouse,
        //     'items' => $this->items,
        ];
    }
}
