<?php

namespace App\Http\Services;

use App\Models\RequestBOM;

class RequestBOMService
{
    public function applyFilters($query)
    {
        if (request()->has('assignment_id')) {
            $query->where('assignment_id', request()->input('assignment_id'));
        }
        if (request()->has('effectivity')) {
            $query->where('effectivity', request()->input('effectivity'));
        }
        return $query;
    }
    public function getAll()
    {
        // return RequestBOM::all();
        $query = RequestBOM::query();
        $query = $this->applyFilters($query);
        return $query->get();
    }

    public function getMyRequest()
    {
        $query = RequestBOM::with(['items'])->where('created_by', auth()->user()->id)->orderBy('created_at', 'DESC');
        $query = $this->applyFilters($query);
        return $query->paginate(10);
    }
    public function getAllRequest()
    {
        $query = RequestBOM::orderBy('created_at', 'DESC');
        $query = $this->applyFilters($query);
        return $query->paginate(10);
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;

        $query = RequestBOM::myApprovals()->with(['items'])->orderBy('created_at', 'DESC');
        $query = $this->applyFilters($query);

        $paginatedResults = $query->paginate(10);

        $filteredResults = $paginatedResults->getCollection()->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();
            return ($nextPendingApproval && $userId === (int)$nextPendingApproval['user_id']);
        });

        $paginatedResults->setCollection($filteredResults);

        return $paginatedResults;
    }

    public function getItemSummary($requestBOM)
    {
        return $requestBOM->items->map(function ($item) {
            $attributes = collect([
                'item_description' => $item->item_description,
                'thickness_val' => $item->thickness_val,
                'thickness_uom' => $item->thickness_uom_symbol,
                'length_val' => $item->length_val,
                'length_uom' => $item->length_uom_symbol,
                'width_val' => $item->width_val,
                'width_uom' => $item->width_uom_symbol,
                'height_val' => $item->height_val,
                'height_uom' => $item->height_uom_symbol,
                'outside_diameter_val' => $item->outside_diameter_val,
                'outside_diameter_uom' => $item->outside_diameter_uom_symbol,
                'inside_diameter_val' => $item->inside_diameter_val,
                'inside_diameter_uom' => $item->inside_diameter_uom_symbol,
                'specification' => $item->specification,
                'volume_val' => $item->volume_val,
                'volume_uom' => $item->volume_uom_symbol,
                'grade' => $item->grade,
                'color' => $item->color,
            ])->filter();

            $itemSummary = $attributes->implode(' ');

            return array_merge([
                'id' => $item->id,
                'item_code' => $item->item_code,
                'item_summary' => $itemSummary,
                'item_description' => $item->item_description,
                'thickness_val' => $item->thickness_val,
                'thickness_uom' => $item->thickness_uom_symbol,
                'length_val' => $item->length_val,
                'length_uom' => $item->length_uom,
                'width_val' => $item->width_val,
                'width_uom' => $item->width_uom,
                'height_val' => $item->height_val,
                'height_uom' => $item->height_uom,
                'outside_diameter_val' => $item->outside_diameter_val,
                'outside_diameter_uom' => $item->outside_diameter_uom,
                'inside_diameter_val' => $item->inside_diameter_val,
                'inside_diameter_uom' => $item->inside_diameter_uom,
                'specification' => $item->specification,
                'volume_val' => $item->volume_val,
                'volume_uom' => $item->volume_uom,
                'grade' => $item->grade,
                'color' => $item->color,
                'uom' => $item->uom,
            ], $attributes->toArray());
        });
    }

    public function hasPendingRequest(string $assignmentType, int $assignmentId, string $effectivity): bool
    {
        return RequestBOM::where('assignment_type', $assignmentType)
            ->where('assignment_id', $assignmentId)
            ->where('effectivity', $effectivity)
            ->where('request_status', 'Pending')
            ->exists();
    }
}
