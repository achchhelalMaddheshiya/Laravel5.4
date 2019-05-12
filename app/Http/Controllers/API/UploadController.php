<?php
namespace App\Http\Controllers\API;

use App\FolderData;
use App\Http\Controllers\Controller;

// Load Model
use App\Http\Traits\UploadTrait;
use App\Interfaces\UploadInterface;
use Illuminate\Http\Request;
use Response;

class UploadController extends Controller implements UploadInterface
{
    use UploadTrait;
    public function Upload(Request $request)
    {
        $folder_data = [];
        $requested_data = $request->all();
       
        $resp = $this->uploadFile($requested_data, $request);
        if ($resp["status"] == 400) {
            return Response::json($resp);
        } else {
            if($requested_data['user_id']){
                $folder_data['user_id'] = $requested_data["data"]['id'];     
            }
            $folder_data['folder_id'] = $requested_data['folder_id'];
            $folder_data['file'] = $resp["data"]["name"];
            $folder_data['extension'] = $resp["data"]["extension"];
            $folder_data["meta_value"] = $resp["data"]["origional_name"];
            $folder_data["meta_key"] = $resp["data"]["type"];
            $folder_data['status'] = 1;
            $folder_data['created_at'] = time();
           
            $created = FolderData::create($folder_data);

            if ($created) {
                $data['status'] = 200;
                $data['message'] = 'Data uploaded successfully';
            }
            return Response::json($data);
        }
    }
}
