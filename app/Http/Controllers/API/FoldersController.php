<?php

namespace App\Http\Controllers\API;

use App\FamilyMember;
use App\FamilyType;
use App\Folder;
use App\FolderData;
use App\FolderPermission;
use App\Notification;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignedFolderRequest;
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\LinksRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Http\Requests\UserVaultRequest;
use App\Http\Requests\WriteFolderRequest;
use App\Http\Traits\CommonTrait;

// Load Model
use App\Interfaces\FolderInterface;
use App\Permission;
use Config;
use DB;
use Illuminate\Http\Request;
use Response;

class FoldersController extends Controller implements FolderInterface
{
    use CommonTrait;
    public function myPersonalVault(Request $request)
    {
        //Get my default folder type=0, assigned to me someone type=2,
        $response = $data = [];
        $requested_data = $request->all();

        $response = Folder::where(['user_id' => $requested_data["data"]["id"], 'status' => 1])->where('type', 0)
            ->select('id', 'name', 'slug', 'type', 'parent_id', 'status', 'created_at')
            ->with(['parentCategory' => function ($q) {
                $q->select('id', 'parent_id', 'name', 'slug');
            }])->first();

        if ($response) {
            $data['data'] = $response;
            $data['status'] = 200;
            $data['message'] = 'Personal vault information';
        } else {
            $data['data'] = [];
            $data['status'] = 400;
            $data['message'] = 'Error in fetching records';
        }
        return Response::json($data);
    }

    public function getVaultDetail(UserVaultRequest $request)
    {
        $requested_data = $request->all();
        // get user personal vault detail Created by me 3, assigned default subfolder to me type=1
        $response = Folder::where(['parent_id' => $request->parent_id, 'user_id' => $requested_data["data"]["id"], 'status' => 1])
            ->whereIn('type', [1, 3])
            ->select('id', 'name', 'slug', 'slug as subfolder_image', 'type', 'parent_id', 'status', 'created_at')
            ->orderBy('created_at', 'asc')->paginate(config('variable.PER_PAGE'));
        if ($response) {
            $data['data'] = $response;
            $data['status'] = 200;
            $data['message'] = 'Personal vault information';
        } else {
            $data['data'] = [];
            $data['status'] = 400;
            $data['message'] = 'No record found';
        }
        return Response::json($data);
    }

    public function createFolder(Request $request)
    {
        //type=3 folder created by me
        $requested_data = $request->all();

        $vault_created = Folder::create([
            'user_id' => $requested_data["data"]["id"],
            'name' => $requested_data["name"],
            'type' => 3,
            'parent_id' => $requested_data["parent_id"],
            'status' => 1,
            'created_at' => time(),
        ]);
        if ($vault_created) {
            $data['data'] = [];
            $data['status'] = 200;
            $data['message'] = 'Folder created successfully';
        } else {
            $data['data'] = [];
            $data['status'] = 400;
            $data['message'] = 'Error while creating folder';
        }
        return Response::json($data);
    }

    public function updateFolder(UpdateFolderRequest $request)
    {
        $requested_data = $request->all();

        $vault_created = Folder::where(["id" => $requested_data["id"]])->update(['name' => $requested_data["name"], 'updated_at' => time()]);
        if ($vault_created) {
            $data['data'] = [];
            $data['status'] = 200;
            $data['message'] = 'Folder updated successfully';
        } else {
            $data['data'] = [];
            $data['status'] = 400;
            $data['message'] = 'Error while updateding folder';
        }
        return Response::json($data);
    }

    public function assignMember(Request $request)
    {
        $created = 0;
        $requested_data = $request->all();
        unset($requested_data['data']);
        if (isset($requested_data["create"]) && count($requested_data["create"]) > 0) {
            FolderPermission::where(['folder_id' => $requested_data["create"][0]['folder_id']])->delete();
            $created = FolderPermission::insert(
                $requested_data["create"]
            );

            /* Save notification for assigned folders*/
            $collection = collect($requested_data["create"]);
            $unique = $collection->unique('user_id');
            $arr = [];
            if (!empty($unique->values()->all())) {
                Notification::where(['folder_id' => $requested_data["create"][0]['folder_id']])->delete();
                $all_family_types = collect($unique->values()->all())->map(function ($name, $key) {
                    $arr["receiver_id"] = $name["user_id"];
                    $arr["folder_id"] = $name["folder_id"];
                    $arr["status"] = 1;
                    $arr["is_read"] = 0;
                    $arr["created_at"] = time();
                    $arr["notification_type"] = 'folder_assigned';
                    return $arr;
                });
                $available_families = $all_family_types->toArray();
                $created = Notification::insert(
                    $available_families
                );
            }

        }
        if ($created) {
            $data['data'] = [];
            $data['status'] = 200;
            $data['message'] = 'User has been successfully assigned to folder';
        } else {
            $data['data'] = [];
            $data['status'] = 400;
            $data['message'] = 'Error while assigning folder';
        }
        return Response::json($data);
    }

    private function checkWriteableData($requested_data, $request)
    {
        switch ($requested_data['attribute_types']) {
            case "links":
                $folder_data['meta_link'] = $requested_data['meta_link'];
                break;
            case "passwords":
                $folder_data['meta_description'] = $requested_data['meta_description'];
                $folder_data['meta_value'] = $requested_data['meta_value'];
                break;
            case "locations":
                $folder_data['meta_description'] = $requested_data['meta_description'];
                $folder_data['meta_key'] = $requested_data['meta_key'];
                $folder_data['meta_value'] = $requested_data['meta_value'];
                $folder_data['lat'] = $requested_data['lat'];
                $folder_data['lng'] = $requested_data['lng'];

                //upload location file
                $resp = $this->uploadFile($requested_data, $request);
                if ($resp["status"] == 400) {
                    return $resp;
                }
                $folder_data['file'] = $resp["message"];
                break;
        }
        $folder_data['folder_id'] = $requested_data['folder_id'];
        $folder_data['meta_key'] = $requested_data['meta_key'];
        $folder_data['attribute_types'] = $requested_data['attribute_types'];
        $folder_data['status'] = 1;
        $folder_data['created_at'] = time();

        return FolderData::create($folder_data);
    }

    public function writeFolder(WriteFolderRequest $request)
    {
        $folder_data = $data = [];
        $requested_data = $request->all();
        $response = $this->checkWriteableData($requested_data, $request);
        if ($response) {
            $data['status'] = 200;
            $data['message'] = 'Record created successfully';
        } else {
            $data['status'] = 400;
            $data['message'] = $response["message"];
        }
        return Response::json($data);
    }

    public function getFolderData(LinksRequest $request)
    {
        // get user personal vault detail Created by me 3, assigned default subfolder to me type=1
        $response = $data = [];
        $requested_data = $request->all();
        $predefined_attr = ['links', 'locations', 'passwords'];
        $query = FolderData::where(['folder_id' => $requested_data['folder_id'], 'status' => 1]);

        if (isset($requested_data['attribute_types']) && $requested_data['attribute_types'] == "locations" && $requested_data['meta_key'] == "go") {
            $query = $query->where('meta_key', 'go');
        }
        if (isset($requested_data['attribute_types']) && $requested_data['attribute_types'] == "locations" && $requested_data['meta_key'] == "been") {
            $query = $query->where('meta_key', 'been');
        }
        if (isset($requested_data['attribute_types']) && !empty($requested_data['attribute_types']) && in_array($requested_data['attribute_types'], $predefined_attr)) {
            $query = $query->where('attribute_types', $requested_data['attribute_types']);
        }
        $query = $query->select('id', 'folder_id', 'attribute_types', 'meta_key', 'meta_value', 'file', 'extension', 'lat', 'lng', 'meta_link', 'meta_description', 'status', 'created_at')
            ->orderBy('created_at', 'desc')->paginate(config('variable.PER_PAGE'));

        if ($query) {
            //Check is owner of this folder
            $is_owner = Folder::where(['id' => $requested_data['folder_id'], "user_id" => $requested_data["data"]["id"], "status" => 1])->count();

            /*$permission_detail = FolderPermission::select('id', 'folder_id', 'permission_id')->with(['permission' => function ($q) {
            $q->select('id', 'name', 'slug');
            }])->where(['folder_id' => $requested_data['folder_id'], 'user_id' => $requested_data["data"]["id"], 'status' => 1])->first();
            if (!empty($permission_detail['permission']) && count($permission_detail['permission']) > 0) {
            $data["permission"]["id"] = $permission_detail['permission'][0]["id"];
            $data["permission"]["name"] = $permission_detail['permission'][0]["name"];
            $data["permission"]["slug"] = $permission_detail['permission'][0]["slug"];
            }*/
            //Query to show data according to the permission user has been given
            //check logged in user permission if permission is upload then show only data uploaded by that user otherwise no record
            $permission_detail = FolderPermission::select('id', 'user_id', 'folder_id', 'permission_id')->with(['permission' => function ($q) {
                $q->select('id', 'name', 'slug')->where('status', 1);
            }, 'folder' => function ($q) {
                $q->select('id', 'user_id', 'name', 'slug');
            }, 'folder.creator' => function ($q) {
                $q->select('id', 'name', 'slug', 'primary_declaration', 'guarantee_declaration', 'status');
            }])->where(['folder_id' => $requested_data['folder_id'], 'user_id' => $requested_data["data"]["id"], 'status' => 1])->get()->toArray();

            if (!empty($permission_detail) && count($permission_detail) > 0) {
                $folder_creator = $permission_detail[0]["folder"]["creator"];

                // My permission array
                $collection = collect($permission_detail)->map(function ($name, $key) {
                    return $name[$key] = $name["permission"][0]["slug"];
                });
                $my_permissions = $collection->toArray();

                //Get all family types avialble in platform
                $all_family = FamilyType::where('status', 1)->select('slug')->get()->toArray();
                $all_family_types = collect($all_family)->map(function ($name, $key) {
                    return $name[$key] = $name['slug'];
                });
                $available_families = $all_family_types->toArray();

                $is_primary_user = FamilyMember::wherehas(
                    'familyTypeDetail', function ($q) {
                        $q->select('id', 'user_id', 'name', 'slug')->where('status', 1);
                    })->with([
                    'familyTypeDetail' => function ($q) {
                        $q->select('id', 'name', 'slug');
                    },
                ])->select('id', 'name', 'status', 'family_id')->where(["invited_by" => $folder_creator["id"], "user_id" => $requested_data["data"]["id"]])->first()->toArray();

                #1-> If i am primary user to this folder or not
                #1.1 If yes then check creator is dead
                if ($folder_creator['primary_declaration'] == 1 && $folder_creator['guarantee_declaration'] == 1 && !empty($is_primary_user['family_type_detail']["slug"]) && in_array($is_primary_user['family_type_detail']["slug"], $available_families)) {

                    //Dead and primary
                    if ($is_primary_user['family_type_detail']["slug"] == "primary") {
                        if (in_array('all', $my_permissions) || in_array('view', $my_permissions) || in_array('download', $my_permissions) || in_array('upload', $my_permissions)) {

                        }
                    } else {
                        //dead and gurantee or member
                        //if permission id upload or download show data uploaded my me and download only that i have uploaded when person is dead and i am other type of user
                        if (in_array('all', $my_permissions) || in_array('view', $my_permissions) || in_array('download', $my_permissions)) {

                        }
                    }
                }

                # Before death
                if (($folder_creator['primary_declaration'] == 0 || $folder_creator['guarantee_declaration'] == 0) && !empty($is_primary_user['family_type_detail']["slug"]) && in_array($is_primary_user['family_type_detail']["slug"], $available_families)) {
                    if ($is_primary_user['family_type_detail']["slug"] == "primary") {

                        if (in_array('upload', $my_permissions) || in_array('download', $my_permissions)) {

                        }

                    } else {
                        // guarantee / member before death , if have upload / download then return my uploaded data + permission
                        if (in_array('upload', $my_permissions) || in_array('download', $my_permissions)) {

                        }
                    }
                }

            }

            $data['is_owner'] = $is_owner;
            $data['data'] = $query;
            $data['status'] = 200;
            $data['message'] = 'Folder information';
        } else {
            $data['permission'] = [];
            $data['data'] = [];
            $data['status'] = 400;
            $data['message'] = 'No record found';
        }
        return Response::json($data);
    }

    public function deleteFolderData(DeleteRequest $request)
    {
        $requested_data = $request->all();
        $folder_data = FolderData::where('id', $requested_data["row_id"])->select('id', 'file')->first();
        $response = FolderData::where('id', $requested_data["row_id"])->delete();
        if ($response) {
            $data['data'] = [];
            $data['status'] = 200;
            $data['message'] = 'Data deleted successfully';
        } else {
            $data['data'] = [];
            $data['status'] = 400;
            $data['message'] = 'There was an error while deleting data';
        }
        return Response::json($data);
    }

    public function getFolderPermissions(Request $request)
    {
        $folders = Permission::where('status', 1)->get();

        if ($folders) {
            $data['status'] = 200;
            $data['message'] = 'Data Retrieved';
            $data['data'] = $folders;
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching data';
            $data['data'] = [];
        }
        return Response::json($data);
    }

    public function getFolderPermissionUsers(Request $request)
    {
        $arr = [];
        $requested_data = $request->all();
        $search = !empty($requested_data["q"]) ? $requested_data["q"] : '';

        $familyProfile = FamilyMember::where(['invited_by' => $requested_data["data"]["id"], "status" => 1])
            ->select('id', 'invited_by', 'user_id', 'name', 'email', 'family_id', 'relation', 'status', 'created_at')
            ->addSelect(DB::raw("false as permission_id"))
            ->whereHas('receiverDetail', function ($q) use ($search) {
                $q->select('id', 'name', 'image', 'slug')->where('status', 1);
                if ($search) {
                    $q->whereRaw("( REPLACE(name,' ','')  LIKE '%" . str_replace(' ', '', $search) . "%')");
                }
            })
            ->with(['receiverDetail' => function ($q) {
                $q->select('id', 'name', 'image', 'slug');
            }, 'familyTypeDetail' => function ($q) {
                $q->select('id', 'name', 'slug')->where('status', 1);
            }, 'relationData' => function ($q) {
                $q->select('id', 'name', 'slug')->where('status', 1);
            }, 'receiverDetail.folderPermissions' => function ($q) use ($requested_data) {
                $q->select('id', 'folder_id', 'user_id', 'status', 'permission_id')->addSelect(DB::raw("false as selected_id"))->where('folder_id', $requested_data['folder_id'])->where('status', 1);
            }, 'receiverDetail.folderPermissions.permission' => function ($q) {
                $q->select('id', 'name', 'slug', 'status');
            }])->orderBy('created_at', 'desc')->get()->toArray();

        if (isset($familyProfile) && !empty($familyProfile)) {
            foreach ($familyProfile as $k => $v) {
                $arr[$k] = $v;
                if (is_array($v["receiver_detail"]["folder_permissions"]) && !empty($v["receiver_detail"]["folder_permissions"])) {
                    $arr[$k]["receiver_detail"]["folder_permissions"][0]["selected_id"] = true;

                    $array = [];
                    foreach ($v["receiver_detail"]["folder_permissions"] as $key => $val) {
                        $array[$key]['row_id'] = $val['id'];
                        $array[$key]['id'] = $val["permission"][0]['id'];
                        $array[$key]['name'] = $val["permission"][0]['name'];
                        $array[$key]['slug'] = $val["permission"][0]['slug'];
                    }
                    $arr[$k]["receiver_detail"]["folder_permissions"][0]["permission"] = $array;
                } else {
                    $arr[$k]["receiver_detail"]["folder_permissions"][0]["selected_id"] = false;
                    $arr[$k]["receiver_detail"]["folder_permissions"][0]["folder_id"] = $requested_data['folder_id'];
                    $arr[$k]["receiver_detail"]["folder_permissions"][0]["permission"] = [];
                }
                $arr[$k]["is_error"] = false;
            }
            return Response::json(["status" => 200, 'message' => 'Family users fetched successfully.', 'data' => (count($arr) > 0) ? $arr : 0]);
        } else {
            return Response::json(["status" => 200, 'message' => 'Family users fetched successfully.', 'data' => []]);
        }
    }
// query to fetch my vault assign by other users
    // MyAssignedFolderRequest
    public function getMyAssignedFolders(Request $request)
    {
        $requested_data = $request->all();
        $folders = FolderPermission::select(
            'folders.user_id as creator_id',
            'users.name as name',
            'users.slug as slug',
            'users.image as image'
        )
            ->join('folders', 'folders.id', '=', 'folder_permissions.folder_id')
            ->join('users', 'users.id', '=', 'folders.user_id')
            ->where('folders.status', 1)
            ->where('users.status', 1)
        //->where('users.primary_declaration', 1)
        //->where('users.guarantee_declaration', 1)
            ->where('folder_permissions.user_id', $requested_data["data"]['id'])
            ->groupBy('users.id')
            ->paginate(config('variable.PER_PAGE'));
        $arr = [];
        if (isset($folders) && !empty($folders)) {
            $arr = $folders->toArray();
            if (isset($arr["data"]) && !empty($arr["data"])) {
                foreach ($arr["data"] as $key => $val) {
                    if (!empty($val["image"]) && file_exists(storage_path() . '/app/public/user/thumb/' . $val["image"])) {
                        $arr["data"][$key]["image"] = config('variable.SERVER_URL') . '/storage/user/thumb/' . $val["image"];
                    } else {
                        $arr["data"][$key]["image"] = config('variable.SERVER_URL') . '/images/user-default.png';
                    }
                }
            }
            $data['status'] = 200;
            $data['message'] = 'Data Retrieved';
            $data['data'] = $arr;
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching data';
            $data['data'] = [];
        }
        return Response::json($data);
    }

    public function getFolderByUser(AssignedFolderRequest $request)
    {
        $requested_data = $request->all();
        $folders = FolderPermission::wherehas('folder.creator', function ($q) use ($requested_data) {
            // 'primary_declaration' => 1, 'guarantee_declaration' => 1
            $q->select('id', 'name', 'slug', 'image')->where(['id' => $requested_data['user_id'], 'status' => 1]);
        })->with(['folder' => function ($q) {
            $q->select('user_id', 'id', 'name', 'slug', 'type', 'parent_id', 'slug as subfolder_image');
        }, 'folder.creator' => function ($q) use ($requested_data) {
            $q->select('id', 'name', 'slug', 'image')->where(['id' => $requested_data['user_id']]);
        }])->where('user_id', $requested_data["data"]['id'])->groupBy('folder_id')->orderBy('created_at', 'desc')->paginate(config('variable.PER_PAGE'));

        if ($folders) {
            $data['status'] = 200;
            $data['message'] = 'Data Retrieved';
            $data['data'] = $folders;
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching data';
            $data['data'] = [];
        }
        return Response::json($data);
    }

    private function getDataByPermission($requested_data, $permission_detail)
    {
        $my_permissions = $folder_creator = [];
        // Folder creator data
        $folder_creator = $permission_detail[0]["folder"]["creator"];

        // My permission array
        $collection = collect($permission_detail)->map(function ($name, $key) {
            return $name[$key] = $name["permission"][0]["slug"];
        });
        $my_permissions = $collection->toArray();

        //Get all family types avialble in platform
        $all_family = FamilyType::where('status', 1)->select('slug')->get()->toArray();
        $all_family_types = collect($all_family)->map(function ($name, $key) {
            return $name[$key] = $name['slug'];
        });
        $available_families = $all_family_types->toArray();

        $is_primary_user = FamilyMember::wherehas(
            'familyTypeDetail', function ($q) {
                $q->select('id', 'user_id', 'name', 'slug')->where('status', 1);
            })->with([
            'familyTypeDetail' => function ($q) {
                $q->select('id', 'name', 'slug');
            },
        ])->select('id', 'name', 'status', 'family_id')->where(["invited_by" => $folder_creator["id"], "user_id" => $requested_data["data"]["id"]])->first()->toArray();

        #1-> If i am primary user to this folder or not
        #1.1 If yes then check creator is dead
        if ($folder_creator['primary_declaration'] == 1 && $folder_creator['guarantee_declaration'] == 1 && !empty($is_primary_user['family_type_detail']["slug"]) && in_array($is_primary_user['family_type_detail']["slug"], $available_families)) {

            //Dead and primary
            if ($is_primary_user['family_type_detail']["slug"] == "primary") {
                if (in_array('all', $my_permissions) || in_array('view', $my_permissions) || in_array('download', $my_permissions) || in_array('upload', $my_permissions)) {
                    //Query to get data for logged in user that is owner of the folder
                    $query = FolderData::where(['folder_id' => $requested_data['folder_id'], 'status' => 1]);
                    $query = $query->select('id', 'folder_id', 'attribute_types', 'meta_key', 'meta_value', 'file as download', 'file as f_name', 'file', 'extension', 'lat', 'lng', 'meta_link', 'meta_description', 'status', 'created_at');

                    if (isset($requested_data['search']) && !empty($requested_data['search'])) {
                        $query = $query->whereRaw("( REPLACE(meta_value,' ','')  LIKE '%" . str_replace(' ', '', $requested_data['search']) . "%')");
                    }
                    $query = $query->orderBy('created_at', 'desc')->paginate(config('variable.PER_PAGE'));
                    $response = $query->toArray();
                    $res["response"] = $response;
                    $res["my_permission"] = $my_permissions;
                    $res["member_type"] = $is_primary_user['family_type_detail']["slug"];
                    $res["is_dead"] = 1;
                    $res["is_upload"] = 0;
                    if (in_array('upload', $my_permissions)) {
                        $res["is_upload"] = 1;
                    }
                    return $res;
                }
            } else {
                //dead and gurantee or member
                //if permission id upload or download show data uploaded my me and download only that i have uploaded when person is dead and i am other type of user
                if (in_array('all', $my_permissions) || in_array('view', $my_permissions) || in_array('download', $my_permissions)) {
                    //Query to get data for logged in user that is owner of the folder
                    $query = FolderData::where(['folder_id' => $requested_data['folder_id'], 'status' => 1]);
                    $query = $query->select('id', 'folder_id', 'attribute_types', 'meta_key', 'meta_value', 'file as download', 'file as f_name', 'file', 'extension', 'lat', 'lng', 'meta_link', 'meta_description', 'status', 'created_at');

                    if (isset($requested_data['search']) && !empty($requested_data['search'])) {
                        $query = $query->whereRaw("( REPLACE(meta_value,' ','')  LIKE '%" . str_replace(' ', '', $requested_data['search']) . "%')");
                    }
                    $query = $query->orderBy('created_at', 'desc')->paginate(config('variable.PER_PAGE'));
                    $response = $query->toArray();
                    $res["response"] = $response;
                    $res["my_permission"] = $my_permissions;
                    $res["member_type"] = $is_primary_user['family_type_detail']["slug"];
                    $res["is_dead"] = 1;
                    $res["is_upload"] = 0;
                    if (in_array('upload', $my_permissions)) {
                        $res["is_upload"] = 1;
                    }
                    return $res;
                }
            }
        }

        # Before death
        if (($folder_creator['primary_declaration'] == 0 || $folder_creator['guarantee_declaration'] == 0) && !empty($is_primary_user['family_type_detail']["slug"]) && in_array($is_primary_user['family_type_detail']["slug"], $available_families)) {

            if ($is_primary_user['family_type_detail']["slug"] == "primary") {
                if (in_array('all', $my_permissions) || in_array('upload', $my_permissions) || in_array('download', $my_permissions)) {
                    $query = FolderData::with([
                        'user' => function ($q) {
                            $q->select('id', 'name', 'slug');
                        },
                    ])->where(['folder_id' => $requested_data['folder_id'], 'user_id' => $requested_data["data"]["id"], 'status' => 1]);
                    $query = $query->select('id', 'folder_id', 'user_id', 'attribute_types', 'meta_key', 'meta_value', 'file as download', 'file as f_name', 'file', 'extension', 'lat', 'lng', 'meta_link', 'meta_description', 'status', 'created_at');
                    if (isset($requested_data['search']) && !empty($requested_data['search'])) {
                        $query = $query->whereRaw("( REPLACE(meta_value,' ','')  LIKE '%" . str_replace(' ', '', $requested_data['search']) . "%')");
                    }
                    $query = $query->orderBy('created_at', 'desc')->paginate(config('variable.PER_PAGE'));
                    $response = $query->toArray();
                    $res["response"] = $response;
                    $res["my_permission"] = $my_permissions;
                    $res["member_type"] = $is_primary_user['family_type_detail']["slug"];
                    $res["is_dead"] = 0;
                    $res["is_upload"] = 0;
                    if (in_array('upload', $my_permissions) || in_array('all', $my_permissions)) {
                        $res["is_upload"] = 1;
                    }
                    return $res;
                }
                // if permission are given only of all then show upload button 2019-03-01
                /*if(in_array('all', $my_permissions)){
            $res["my_permission"] = $my_permissions;
            $res["member_type"] = $is_primary_user['family_type_detail']["slug"];
            $res["is_upload"] = 1;
            }*/
            } else {

                // guarantee / member before death , if have upload / download then return my uploaded data + permission
                if (in_array('all', $my_permissions) || in_array('upload', $my_permissions) || in_array('download', $my_permissions)) {
                    $query = FolderData::with([
                        'user' => function ($q) {
                            $q->select('id', 'name', 'slug');
                        },
                    ])->where(['folder_id' => $requested_data['folder_id'], 'user_id' => $requested_data["data"]["id"], 'status' => 1]);
                    $query = $query->select('id', 'folder_id', 'user_id', 'attribute_types', 'meta_key', 'meta_value', 'file as download', 'file as f_name', 'file', 'extension', 'lat', 'lng', 'meta_link', 'meta_description', 'status', 'created_at');
                    if (isset($requested_data['search']) && !empty($requested_data['search'])) {
                        $query = $query->whereRaw("( REPLACE(meta_value,' ','')  LIKE '%" . str_replace(' ', '', $requested_data['search']) . "%')");
                    }
                    $query = $query->orderBy('created_at', 'desc')->paginate(config('variable.PER_PAGE'));
                    $response = $query->toArray();
                    $res["response"] = $response;
                    $res["my_permission"] = $my_permissions;
                    $res["member_type"] = $is_primary_user['family_type_detail']["slug"];
                    $res["is_dead"] = 0;
                    $res["is_upload"] = 0;
                    if (in_array('upload', $my_permissions) || in_array('all', $my_permissions)) {
                        $res["is_upload"] = 1;
                    }
                    return $res;
                }

                // if permission are given only of all then show upload button 2019-03-01
                /*if(in_array('all', $my_permissions)){
            $res["my_permission"] = $my_permissions;
            $res["member_type"] = $is_primary_user['family_type_detail']["slug"];
            $res["is_upload"] = 1;
            }*/
            }
        }

    }
    public function getFolderDetail(LinksRequest $request)
    {
        $requested_data = $request->all();
        //Query to get data for logged in user that is owner of the folder
        $query = FolderData::where(['folder_id' => $requested_data['folder_id'], 'status' => 1]);
        $query = $query->select('id', 'folder_id', 'attribute_types', 'meta_key', 'meta_value', 'file as download', 'file as f_name', 'file', 'extension', 'lat', 'lng', 'meta_link', 'meta_description', 'status', 'created_at');

        if (isset($requested_data['search']) && !empty($requested_data['search'])) {
            $query = $query->whereRaw("( REPLACE(meta_value,' ','')  LIKE '%" . str_replace(' ', '', $requested_data['search']) . "%')");
        }
        $query = $query->orderBy('created_at', 'desc')->paginate(config('variable.PER_PAGE'));
        $query['response'] = $query->toArray();

        //Query to show data according to the permission user has been given
        //check logged in user permission if permission is upload then show only data uploaded by that user otherwise no record
        $permission_detail = FolderPermission::select('id', 'user_id', 'folder_id', 'permission_id')->with(['permission' => function ($q) {
            $q->select('id', 'name', 'slug')->where('status', 1);
        }, 'folder' => function ($q) {
            $q->select('id', 'user_id', 'name', 'slug');
        }, 'folder.creator' => function ($q) {
            $q->select('id', 'name', 'slug', 'primary_declaration', 'guarantee_declaration', 'status');
        }])->where(['folder_id' => $requested_data['folder_id'], 'user_id' => $requested_data["data"]["id"], 'status' => 1])->get()->toArray();

        if (!empty($permission_detail) && count($permission_detail) > 0) {
            $query = $this->getDataByPermission($requested_data, $permission_detail);
        }

        if ($query) {
            //Check is owner of this folder
            $is_owner = Folder::where(['id' => $requested_data['folder_id'], "user_id" => $requested_data["data"]["id"], "status" => 1])->count();
            $data["my_permissions"] = $query["my_permission"];
            $data['is_owner'] = $is_owner;
            $data['user_id'] = $requested_data["data"]["id"];
            $data['data'] = !empty($query["response"]) ? $query["response"] : [];
            $data['member_type'] = $query["member_type"];
            $data['is_dead'] = (isset($query["is_dead"]) && $query["is_dead"] === 1) ? 1 : 0;
            $data['is_upload'] = $query["is_upload"];

            $data['folder_data'] = Folder::with([
                'creator' => function ($q) {
                    $q->select('id', 'name', 'slug', 'primary_declaration', 'guarantee_declaration', 'status');
                },
            ])->where(['id' => $requested_data['folder_id'], 'status' => 1])->select('id', 'name', 'user_id', 'slug', 'parent_id')->first();
            $data['status'] = 200;
            $data['message'] = 'Folder information';
        } else {
            $data['is_upload'] = 0;
            $data["my_permissions"] = [];
            $data['member_type'] = '';
            $data['is_dead'] = '';
            $data['user_id'] = $requested_data["data"]["id"];
            $data['folder_data'] = Folder::with([
                'creator' => function ($q) {
                    $q->select('id', 'name', 'slug', 'primary_declaration', 'guarantee_declaration', 'status');
                },
            ])->where(['id' => $requested_data['folder_id'], 'status' => 1])->select('id', 'name', 'user_id', 'slug', 'parent_id')->first();
            $data['data'] = [];
            $data['status'] = 400;
            $data['message'] = 'No record found';
        }
        return Response::json($data);
    }

}
