<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class Notification extends Authenticatable
{
    public $timestamps = false;
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'receiver_id', 'invited_by','folder_id', 'notification_type','is_read','email','family_id','status','created_at','updated_at'
    ];

    
    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'invited_by');
    }
    public function sender()
    {
        return $this->hasOne(User::class, 'id', 'sender_id');
    }

    public function receiver()
    {
        return $this->hasOne(User::class, 'id', 'receiver_id');
    }

    public function familyType()
    {
        return $this->hasOne(FamilyType::class, 'id', 'family_id');
    }
    
    public function folder()
    {
        return $this->hasOne(Folder::class, 'id', 'folder_id');
    }
}
