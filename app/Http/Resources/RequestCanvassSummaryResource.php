<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestCanvassSummaryResource extends JsonResource
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
            'cs_number' => $this->cs_number,
            'terms_of_payment' => $this->terms_of_payment,
            'availability' => $this->availability,
            'delivery_terms' => $this->delivery_terms,
            'created_at' => $this->createdAtDateHuman,
        ];
    }
}
