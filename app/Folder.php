<?php

namespace App;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Config;
class Folder extends Model
{
    use Sluggable;
    public $timestamps = false;

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }
    protected $fillable = [
        'user_id', 'slug', 'name', 'type', 'parent_id', 'status', 'created_at', 'updated_at',
    ];

    public function parentCategory()
    {
        return $this->hasOne(Folder::class, 'id', 'parent_id');
    }

    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    // path of documet
    public function getSubfolderImageAttribute($value)
    {
        $server_url = Config::get('variable.SERVER_URL');
        $url = '';
        $url = '/images/folder-icon.png';

        if ($value == 'links') {
            $url = '/images/icon-09.png';
        }
        if ($value == 'locations') {
            $url = '/images/icon-10.png';
        }
        if ($value == 'passwords') {
            $url = '/images/icon-11.png';
        }
        return $server_url . $url;
    }
}
