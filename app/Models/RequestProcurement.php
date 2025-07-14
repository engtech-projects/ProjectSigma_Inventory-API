<?php

namespace App\Models;

use App\Enums\AccessibilityInventory;
use App\Enums\UserTypes;
use App\Http\Traits\CheckAccessibility;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class RequestProcurement extends Model
{
    use HasFactory;
    use HasApiTokens;
    use Authorizable;
    use Notifiable;
    use SoftDeletes;
    use ModelHelpers;
    use CheckAccessibility;

    protected $fillable = [
        'request_requisition_slip_id',
        'serve_status',
    ];

    public function requestStock()
    {
        return $this->belongsTo(RequestStock::class, 'request_requisition_slip_id');
    }

    public function canvassers()
    {
        return $this->belongsToMany(User::class, 'request_procurement_canvassers', 'request_procurement_id', 'user_id');
    }

    public function scopeIsUnserved($query)
    {
        return $query->where('serve_status', 'unserved');
    }

    public function scopeIsCanvasser($query, $userId)
    {

        $user = Auth::user();
        $userAccessibilitiesNames = $user->accessibilities_name;
        $isUserSetCanvasser = $this->checkUserAccessManual($userAccessibilitiesNames, [AccessibilityInventory::INVENTORY_PROCUREMENT_PROCUREMENTREQUESTS_SETCANVASSER->value]) || Auth::user()->type == UserTypes::ADMINISTRATOR->value;
        return $query->when($isUserSetCanvasser, function($query) use($userId) {
            $query->whereHas('canvassers', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            });
        });
    }

    public function priceQuotations()
    {
        return $this->hasMany(PriceQuotation::class, 'request_procurement_id');
    }


}
