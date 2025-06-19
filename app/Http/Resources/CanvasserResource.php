<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CanvasserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // parent::toArray($request);
        return [
            "id" => $this->id,
            "fullname_first" => optional($this->employee)->fullname_first,
            "fullname_last" => optional($this->employee)->fullname_last,
        ];
    }
}
