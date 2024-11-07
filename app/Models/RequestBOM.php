<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class RequestBOM extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasApproval;


    protected $table = 'request_bom';
    protected $fillable = [
        'assignment_id',
        'assignment_type',
        'effectivity',
        'approvals',
        'created_by',
        'request_status',
    ];

    protected $casts = [
        'approvals' => 'array',
        'effectivity' => 'string',
    ];

    public $appends = [
        'item_summary'
    ];

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function scopeRequestStatusPending(Builder $query): void
    {
        $query->where('request_status', RequestApprovalStatus::PENDING);
    }

    public function scopeAuthUserPending(Builder $query): void
    {
        // Assuming authUserPending logic
        $query->where('created_by', auth()->user()->id);
    }

    public function completeRequestStatus()
    {
        $this->request_status = RequestApprovalStatus::APPROVED;
        $this->save();
        $this->refresh();
    }

    public function getItemSummaryAttribute()
    {
        return $this->items->map(function ($item) {


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

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function assignment()
    {
        return $this->morphTo();
    }

    public function details()
    {
        return $this->hasMany(Details::class, 'request_bom_id');
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(
            ItemProfile::class,
            Details::class,
            'request_bom_id',
            'id',
            'id',
            'item_id'
        );
    }

    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */
}
