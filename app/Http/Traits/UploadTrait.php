<?php
namespace App\Http\Traits;

use App\Category;
use App\Folder;
use App\FolderData;
use App\FolderPermission;
use Image;
use Storage;
trait UploadTrait
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

    private function getCountByExtension($requested_data, $ext)
    {
        /*if($requested_data['user_id'] == true ){
        // Get Total Upload By the creator  + Assignee
        // Get creator id from the folder id

        $invintee_count = FolderData::whereIn('extension', $ext)->where(['user_id' => $requested_data['data']['id'],'status' => 1])->count();
        return $invintee_count;
        }else{
        die('enddddddd');
        $count = FolderData::wherehas('folder')->wherehas('folder.creator', function($q) use($requested_data){
        $q->where(["user_id" => $requested_data['data']['id']]);
        })->with(['folder.creator'])->whereIn('extension', $ext)->where('attribute_types', '=','')->where(['status' => 1])->count();
        return $count;
        }*/
        if ($requested_data['user_id'] === "true") {
            $count = $invintee_count = $creator_count = 0;
            // Get Total Upload By the creator  + Assignee
            // Get creator id from the folder id
            $creator_id = Folder::where('id', $requested_data["folder_id"])->first()->user_id;
            $creator_count = $this->getCreatorUploadCount($creator_id, $requested_data, $ext);
            // Get assignee total uploads from all assigned folders to him for this creator
            $invintee_count = $this->getCreatorInviteUploadCount($creator_id, $requested_data, $ext);
            $count = $creator_count + $invintee_count;
            return $count;
            // $invintee_count = FolderData::whereIn('extension', $ext)->where(['user_id' => $requested_data['data']['id'], 'status' => 1])->count();

        } else {
            $count = $asignee_count = 0;
            // Get My count that is uploaded ny me on any of my folder
            $count = $this->getMyUploadCount($requested_data, $ext);
            //Get count of any of the asignee
            $asignee_count = $this->getMyAsigneeUploadCount($requested_data, $ext);
            return $count + $asignee_count;
        }
    }
    private function getCreatorUploadCount($creator_id, $requested_data, $ext)
    {
        // Get creators total uploads
        return FolderData::wherehas('folder', function ($q) {
            $q->where(["type" => 3]);
        })->wherehas('folder.creator', function ($q) use ($creator_id) {
            $q->where(["id" => $creator_id]);
        })->with(['folder.creator' => function ($q) use ($creator_id) {
            $q->where(["id" => $creator_id])->select('id', 'name', 'slug');
        }])->whereIn('extension', $ext)->where('attribute_types', '=', '')->where(['status' => 1])->count();
    }

    private function getCreatorInviteUploadCount($creator_id, $requested_data, $ext)
    {
        $response = FolderPermission::wherehas('folder', function ($q) {
            $q->where(["type" => 3])->select('id', 'name', 'slug');
        })->wherehas('folder.creator', function ($q) use ($creator_id) {
            $q->where(["id" => $creator_id])->select('id', 'name', 'slug');
        })->with(['folder', 'folder.creator' => function ($q) use ($creator_id) {
            $q->where(["id" => $creator_id])->select('id', 'name', 'slug');
        }])->select('id', 'folder_id', 'user_id')->where(["user_id" => $requested_data['data']['id']])->groupBy('folder_id')->get()->toArray();

        $collection = collect($response)->map(function ($name, $key) {
            return $name[$key] = $name["folder_id"];
        });
        $folder_ids = $collection->toArray();

        $invintee_count = FolderData::where(function ($q) use ($folder_ids, $ext) {
            $q->whereIn('folder_id', $folder_ids)
                ->whereIn('extension', $ext);
        })->where(['user_id' => $requested_data['data']['id'], 'status' => 1])->count();
        return $invintee_count;
    }
    private function getMyUploadCount($requested_data, $ext)
    {
        return FolderData::wherehas('folder', function ($q) {
            $q->where(["type" => 3]);
        })->wherehas('folder.creator', function ($q) use ($requested_data) {
            $q->where(["id" => $requested_data['data']['id']]);
        })->with(['folder.creator'])->whereIn('extension', $ext)->where('attribute_types', '=', '')->where(['status' => 1])->count();
    }

    private function getMyAsigneeUploadCount($requested_data, $ext)
    {
        return FolderData::wherehas('folder', function ($q) {
            $q->where(["type" => 3]);
        })->wherehas('folder.creator', function ($q) use ($requested_data) {
            $q->where(["id" => $requested_data['data']['id']]);
        })->with(['folder.creator'])->whereIn('extension', $ext)
            ->where('attribute_types', '=', '')
            ->whereNotNull('user_id')
            ->where(['status' => 1])
            ->count();
    }

    public function uploadFile($requested_data, $request)
    {
        $dir_main = '';
        $data = [];

        if (!Storage::disk('public')->exists('files/')) {
            Storage::disk('public')->makeDirectory('files/', 0777, true);
            $dir_main = 'public/files/';
        } else {
            $dir_main = 'public/files/';
        }
        // check file extension
        $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $audio_ext = ['mp3', 'ogg', 'mpga'];
        $video_ext = ['mp4', 'mpeg'];
        $document_ext = ['doc', 'docx', 'pdf', 'odt'];

        $allowed = array_merge($image_ext, $audio_ext, $video_ext, $document_ext);
        $filename = $_FILES['file']['name'];
        $type = $_FILES['file']['type'];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if (!in_array($ext, $allowed)) {
            $msg = 'Invalid file format. Allowed format : ' . implode(', ', $allowed);
            $data['status'] = 400;
            $data['message'] = $msg;
            return $data;
        }
        if (in_array($ext, $allowed)) {
            $size = $_FILES['file']['size'];
            $allowed_size = 2097152;
            if (in_array($ext, $image_ext)) {
                $limit = $requested_data['data']['packages']['details']['image_limit'];
                $count = $this->getCountByExtension($requested_data, $image_ext);
                $content = implode(', ', $image_ext);
            }

            if (in_array($ext, $audio_ext)) {
                $limit = $requested_data['data']['packages']['details']['audio_limit'];
                $count = $this->getCountByExtension($requested_data, $audio_ext);
                $content = implode(', ', $audio_ext);
            }

            if (in_array($ext, $video_ext)) {
                $limit = $requested_data['data']['packages']['details']['video_limit'];
                $count = $this->getCountByExtension($requested_data, $video_ext);
                $content = implode(', ', $video_ext);
            }

            if (in_array($ext, $document_ext)) {
                $limit = $requested_data['data']['packages']['details']['document_limit'];
                $count = $this->getCountByExtension($requested_data, $audio_ext);
                $content = implode(', ', $audio_ext);
            }

            if ($count >= $limit) {
                $data['status'] = 400;
                $data['message'] = 'You have exceeded the total limit (' . $limit . ') of adding ' . $content;
                return $data;
            }
            if ($size > $allowed_size) {
                $data['status'] = 400;
                $data['message'] = 'File too large. Max upload size 2MB';
                return $data;
            }
        }
        // die('end');
        $dynamic_name = time() . '-' . $this->imageDynamicName() . '.' . $ext;
        $image = $request->file('file')->storeAs($dir_main, $dynamic_name);

        if ($image) {
            $image_name = explode('/', $image);
            $name_created = end($image_name);
            if (in_array($ext, $image_ext)) {
                $saved_Image = $this->imageVersions($name_created, 338, 338);
            } else {
                $saved_Image = true;
            }
            if ($saved_Image) {
                $data['status'] = 200;
                $data['message'] = 'Upload successfully';
                $data['data']["origional_name"] = $filename;
                $data['data']["name"] = $name_created;
                $data['data']["extension"] = $ext;
                $data['data']["type"] = $type;
            } else {
                $data['status'] = 400;
                $data['message'] = 'Error in uploading thumbnails';
                $data['data'] = [];
            }
            return $data;
        }
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

    public function uploadAdFile($requested_data, $request)
    {
        $dir_main = '';
        $data = [];
        $category_data = Category::where(['id' => $requested_data["category_id"]])->first();
        $imageInformation = getimagesize($request->file('file'));
        if ($imageInformation[0] == $category_data->width && $imageInformation[1] == $category_data->height) {
            if (!Storage::disk('public')->exists('ads/')) {
                Storage::disk('public')->makeDirectory('ads/', 0777, true);
                $dir_main = 'public/ads/';
            } else {
                $dir_main = 'public/ads/';
            }
            // check file extension
            $image_ext = ['jpg', 'jpeg', 'png', 'gif'];

            $allowed = $image_ext;
            $filename = $_FILES['file']['name'];
            $type = $_FILES['file']['type'];

            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if (!in_array($ext, $allowed)) {
                $msg = 'Invalid file format. Allowed format : ' . implode(', ', $allowed);
                $data['status'] = 400;
                $data['message'] = $msg;
                return $data;
            }
            if (in_array($ext, $allowed)) {
                $size = $_FILES['file']['size'];
                $allowed_size = 2097152;
                if ($size > $allowed_size) {
                    $data['status'] = 400;
                    $data['message'] = 'File too large. Max upload size 2MB';
                    return $data;
                }
            }

            $dynamic_name = time() . '-' . $this->imageDynamicName() . '.' . $ext;
            $image = $request->file('file')->storeAs($dir_main, $dynamic_name);

            if ($image) {
                $image_name = explode('/', $image);
                $name_created = end($image_name);
                if ($name_created) {
                    $data['status'] = 200;
                    $data['message'] = 'Upload successfully';
                    $data['data']["name"] = $name_created;
                } else {
                    $data['status'] = 400;
                    $data['message'] = 'Error in uploading thumbnails';
                    $data['data'] = [];
                }
                return $data;
            }
        } else {
            $msg = 'Image width & height should be equal to ' . $category_data->width . ',' . $category_data->height . ' respectively ';
            $data['status'] = 400;
            $data['message'] = $msg;
            return $data;
        }
    }
}
