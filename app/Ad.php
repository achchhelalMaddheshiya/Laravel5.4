<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Config;
use Storage;
class Ad extends Model
{
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'link', 'file', 'status', 'created_at', 'updated_at',
    ];

    public function stats()
    {
        return $this->hasMany(AdStat::class, 'ad_id', 'id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function getFileAttribute($value)
    {
        $img = '';
        $server_url = Config::get('variable.SERVER_URL');
        if (!empty($value) && Storage::disk('public')->exists('ads/' . $value)) {
            @chmod('/storage/ads/' . $value);
            $img = '/storage/ads/' . $value;
            return $server_url.$img;
        } 
    }
}
