<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
class Package extends Model
{
    use Sluggable;
    
    public $timestamps = false;
    protected $fillable = ['name', 'slug', 'description', 'audio_limit', 'video_limit', 'document_limit', 'image_limit', 'members_count_limit', 'amount','subscription_days', 'status', 'created_at'];

    public function sluggable() {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
   
}
