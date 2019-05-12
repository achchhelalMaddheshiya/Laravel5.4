<?php

namespace App;

use Config;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    public $timestamps = false;
    use Notifiable, HasApiTokens, Sluggable, Billable;

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'email', 'password', 'verify_token', 'secondary_email', 'secondary_expiry', 'email_verification_code', 'role_id', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function AauthAcessToken()
    {
        return $this->hasMany('\App\OauthAccessToken');
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function packages()
    {
        return $this->hasOne(UserPackage::class, 'user_id', 'id');
    }

    // path of documet
    public function getImageAttribute($value)
    {
        $server_url = Config::get('variable.SERVER_URL');
        if (!empty($value) && file_exists(storage_path() . '/app/public/user/thumb/' . $value)) {
            return $server_url . '/storage/user/thumb/' . $value;
        } else {
            return $server_url . '/images/user-default.png';
        }
    }

    public function notification()
    {
        return $this->hasMany(Notification::class, 'receiver_id', 'id')->where('is_read', 0);
    }

    public function folderPermissions()
    {
        return $this->hasMany(FolderPermission::class, 'user_id', 'id');
    }

}
