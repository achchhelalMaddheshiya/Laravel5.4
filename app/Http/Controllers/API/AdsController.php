<?php
namespace App\Http\Controllers\API;

use App\Ad;
use App\AdStat;
use App\Http\Controllers\Controller;
use App\Http\Traits\UploadTrait;
use App\Interfaces\AdInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Response;

class AdsController extends Controller implements AdInterface
{
    use UploadTrait;
    //
    public function __construct(Request $request, Route $route)
    {
        $parsed_token = $request->headers->all();
        if (isset($parsed_token['authorization'][0])) {
            $token = $parsed_token['authorization'][0];
        }
        $method = $route->getActionName();
        $method = explode('@', $method);
        $action = $method[1];
        if ($action == 'saveAdStats' && !empty($token)) {
            $this->middleware(['auth:api', 'user_data'])->only('saveAdStats');
        }
    }
    
    public function getAds(Request $request)
    {
        $requested_data = $request->all();

        $ads = Ad::whereHas('category', function ($q) use ($requested_data) {
            $q->where('slug', $requested_data['slug']);
        })->with(['category' => function ($q) {
            $q->select('id', 'name', 'width', 'height');
        }])->select('id', 'category_id', 'link', 'file')->where('status', 1)->first();

        if ($ads) {
            $data["data"] = $ads;
            $data["status"] = 200;
            $data["message"] = 'Ad created successfully';
        } else {
            $data["data"] = '';
            $data["status"] = 400;
            $data["message"] = 'Not able to create ad';
        }
        return Response::json($data);
    }

    public function createAd(Request $request)
    {
        $requested_data = $request->all();
        $result = Ad::where(['category_id' => $requested_data["category_id"]])->first();
        if (isset($result) && !empty($result)) {
            //Delete Old data from dir also
            if (!empty($result->file) && Storage::disk('public')->exists('ads/' . $result->file)) {
                Storage::disk('public')->delete('ads/' . $result->file);
            }
            Ad::where('id', $result->id)->delete();
        }
        $resp = $this->uploadAdFile($requested_data, $request);

        if ($resp["status"] == 400) {
            return Response::json($resp);
        } else {
            $created = Ad::create([
                "category_id" => $requested_data["category_id"],
                "link" => $requested_data["link"],
                "file" => $resp["data"]["name"],
                'status' => 1,
                'created_at' => time(),
            ]);

            if ($created) {
                $data["status"] = 200;
                $data["message"] = 'Ad created successfully';
            } else {
                $data["status"] = 400;
                $data["message"] = 'Not able to create ad';
            }
        }

        return Response::json($data);
    }

    public function saveAdStats(Request $request)
    {
        $requested_data = $request->all();
        $response = Ad::where(["id" => $requested_data["id"]])->first();
        if (isset($response) && !empty($response)) {

            $created = AdStat::updateOrCreate(
                [
                    'ad_id' => $requested_data["id"],
                    'ip' => $_SERVER['REMOTE_ADDR'],
                ],
                [
                    'ad_id' => $requested_data["id"],
                    'user_id' => !empty($requested_data["data"]["id"]) ? $requested_data["data"]["id"] : '',
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'status' => 1,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );

            /*$created = AdStat::create([
            'ad_id' => $requested_data["id"],
            'user_id' => ($requested_data["data"]["id"] != '') ? $requested_data["data"]["id"] : '',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'status' => 1,
            'created_at' => time(),
            ]);*/
            if ($created) {
                $data["status"] = 200;
                $data["message"] = 'Stats created successfully';
            } else {
                $data["status"] = 400;
                $data["message"] = 'Not able to create ad';
            }
            return Response::json($data);
        }
    }

}
