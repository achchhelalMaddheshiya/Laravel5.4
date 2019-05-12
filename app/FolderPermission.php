<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FolderPermission extends Model
{
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'folder_id', 'user_id', 'permission_id','status','created_at','updated_at'
    ];


    public function permission()
    {
        return $this->hasMany(Permission::class, 'id', 'permission_id');
    }

    public function folder()
    {
        return $this->hasOne(Folder::class, 'id', 'folder_id');
    }

}
