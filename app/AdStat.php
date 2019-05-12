<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdStat extends Model
{
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ad_id', 'user_id', 'ip', 'status', 'created_at', 'updated_at',
    ];

    public function ad()
    {
        return $this->hasOne(Category::class, 'id', 'ad_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
