<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Model implements AuthenticatableContract
{
    use HasApiTokens;
    use HasFactory;
    use Authorizable;
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'hrms_id',
        'type',
        'accessibilities',
        'name',
        'email',
        'email_verified_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];
    public function getAuthIdentifierName()
    {
        return [
            'user_id' => 'id',
            'email' => 'email',
            'name' => 'name',
            'type' => 'user',
            'accessibilities' => 'accessibilities'
        ];
    }
    public function getAuthIdentifier()
    {
        return $this->getAttributeFromArray('user_id');
    }
    public function getAuthPassword()
    {
        return null;
    }
    public function getRememberToken()
    {
        return null;
    }
    public function setRememberToken($value)
    {
    }
    public function getRememberTokenName()
    {
    }

    public function getAccessibilities()
    {
        $accessibilities = $this->getAttributeFromArray('accessibilities');
        $userAcess = [];
        $accessGroup = 'inventory:';
        foreach ($accessibilities as $key => $value) {
            if (str_starts_with($value, $accessGroup)) {
                array_push($userAcess, $value);
            }
        }
        return $accessibilities;
    }

    public function receiveBroadcastNotification()
    {
        return 'users.' . $this->id;
    }

    public function procurementRequests()
    {
        return $this->belongsToMany(RequestProcurementCanvasser::class, 'request_procurement_canvassers');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    //     'password' => 'hashed',
    // ];
}
