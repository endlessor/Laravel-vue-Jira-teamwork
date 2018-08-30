<?php

namespace App;

use App\JIRA\Tenant;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * @param Tenant $tenant
     * @param $key
     * @return User|null
     */
    public static function fromKey(Tenant $tenant, $key)
    {
        return User
            ::where('tenant_id', $tenant->id)
            ->where('key', $key)
            ->get()
            ->first();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
