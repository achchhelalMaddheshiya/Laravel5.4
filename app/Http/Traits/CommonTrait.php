<?php
namespace App\Http\Traits;
use App\Notification;
use Image;
use Storage;
trait CommonTrait
{
    public function imageDynamicName()
    {
        #Available alpha caracters
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pin = mt_rand(1000000, 9999999)
            . $characters[rand(0, 5)];
        $string = str_shuffle($pin);
        return $string;
    }

    public function uploadFile($requested_data, $request)
    {

        $dir_main = '';
        $data = [];

        if (!Storage::disk('public')->exists('files/')) {
            Storage::disk('public')->makeDirectory('files/', 0777, true); //creates directory'not exists';
            $dir_main = 'public/files/';
        } else {
            $dir_main = 'public/files/';
        }
        // check file extension
        $allowed = ['jpeg', 'png', 'jpg'];
        $filename = $_FILES['file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $data['status'] = 400;
            $data['message'] = 'Invalid file format. Allowed format : jpg,jpeg,png';
        }
        // check file size
        if ($_FILES['file']['size'] > 2097152) {
            $data['status'] = 400;
            $data['message'] = 'File too large. Max upload size 2MB';
        }
        $dynamic_name = time() . '-' . $this->imageDynamicName() . '.' . $ext;
        $image = $request->file('file')->storeAs($dir_main, $dynamic_name);

        if ($image) {
            $image_name = explode('/', $image);
            $name_created = end($image_name);
            $saved_Image = $this->imageVersions($name_created, 338, 338);
            if ($saved_Image) {
                $data['status'] = 200;
                $data['message'] = $name_created;
            } else {
                $data['status'] = 400;
                $data['message'] = 'Error in uploading thumbnails';
            }
        }
        return $data;
    }

    public function imageVersions($name, $width = null, $height = null)
    {
        if ($width != null) {$width = $width;} else { $width = 179;}
        if ($height != null) {$height = $height;} else { $height = 179;}

        if (!Storage::disk('public')->exists('files/')) {
            Storage::disk('public')->makeDirectory('files/', 0777, true); //creates directory if 'not exists';
            if (!Storage::disk('public')->exists('files/thumb')) {
                Storage::disk('public')->makeDirectory('files/thumb', 0777, true); //creates directory if 'not exists';
            }
        } else {
            if (!Storage::disk('public')->exists('files/thumb')) {
                Storage::disk('public')->makeDirectory('files/thumb', 0777, true); //creates directory if 'not exists';

            }
        }

        if (Storage::disk('public')->exists('files/' . $name)) {
            $contents = storage_path('app/public/files/' . $name);
            $saved = Image::make($contents)->resize($width, $height)->save(storage_path('app/public/files/thumb/' . $name));

            if ($saved) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    public function saveNotifications(array $arr){
        Notification::create($arr);
    }
}
