<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    //
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'package_id','user_id','charge_id', 'type','status','current_period_start', 'current_period_end', 'canceled_at','cancel_at_period_end','amount','balance_transaction','stripe_id','stripe_plan','created_at','updated_at'
    ];
   
    public function details() {
        return $this->hasOne(Package::class,'id','package_id');
    }
}
