<?php

namespace App;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use  Sluggable;
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
        'name', 'slug',  'status', 'created_at', 'updated_at'
    ];
}
