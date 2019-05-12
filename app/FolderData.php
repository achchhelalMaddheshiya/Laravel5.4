<?php
namespace App;

use Config;
use Illuminate\Database\Eloquent\Model;
use Storage;

class FolderData extends Model
{
    public $timestamps = false;
    protected $table = 'folder_data';

    protected $fillable = [
        'folder_id', 'user_id', 'attribute_types', 'meta_key', 'meta_value', 'meta_link', 'meta_description', 'file', 'extension', 'lat', 'lng', 'status', 'created_at', 'updated_at',
    ];

    public function getFileAttribute($value)
    {
        $img = '';
        $ext = $this->extension;
        $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $audio_ext = ['mp3', 'ogg', 'mpga'];
        $video_ext = ['mp4', 'mpeg'];
        $document_ext = ['doc', 'docx', 'pdf', 'odt'];

        $server_url = Config::get('variable.SERVER_URL');

        if (in_array($ext, $image_ext) && !empty($image_ext)) {
            if (!empty($value) && Storage::disk('public')->exists('files/thumb/' . $value)) {
                $img = '/storage/files/thumb/' . $value;
            }else{
                $img = '/images/user-default.png';
            }
            return $server_url . $img;
        } else {
            if (in_array($ext, $audio_ext) && !empty($audio_ext)) {
                $img = '/images/audio-icon.jpg';
            }

            if (in_array($ext, $video_ext) && !empty($video_ext)) {
                $img = '/images/video-icon.jpg';
            }

            if (in_array($ext, $document_ext) && !empty($document_ext)) {
                $img = '/images/doc-icon.jpg';
            }
            if ($this->attribute_types == 'locations') {
                $img = '/images/map.jpg';
            }
            return $server_url . $img;
        }
    }

    public function getDownloadAttribute($value)
    {
        $img = '';
        $ext = $this->extension;
        $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $audio_ext = ['mp3', 'ogg', 'mpga'];
        $video_ext = ['mp4', 'mpeg'];
        $document_ext = ['doc', 'docx', 'pdf', 'odt'];

        $server_url = Config::get('variable.SERVER_URL');

        if (in_array($ext, $image_ext) && !empty($image_ext)) {
            if (!empty($value) && Storage::disk('public')->exists('files/thumb/' . $value)) {
                return $server_url . '/storage/files/' . $value;
            }
        } else {
            return $server_url . '/storage/files/' . $value;
        }
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


    public function folder()
    {
        return $this->hasOne(Folder::class, 'id', 'folder_id');
    }
}
