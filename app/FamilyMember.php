<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class FamilyMember extends Authenticatable
{
    public $timestamps = false;
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invited_by', 'user_id', 'name','email','image','dob','family_id','location','relation','postcode','lng','lat','code','status','created_at','updated_at'
    ];

    public function senderDetail() {
        return $this->belongsTo('App\User','invited_by','id');
    }

    public function receiverDetail() {
        return $this->belongsTo('App\User','user_id','id');
    }

    public function familyTypeDetail() {
        return $this->belongsTo('App\FamilyType','family_id','id');
    }

    public function relationData() {
        return $this->belongsTo('App\Relation','relation','id');
    }    

    
}