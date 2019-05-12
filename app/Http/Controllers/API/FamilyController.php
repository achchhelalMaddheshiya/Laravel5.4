<?php
//redis-server
//sudo supervisorctl start all
namespace App\Http\Controllers\API;

use App\FamilyMember;
use App\FamilyType;
use App\FolderPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteMemberRequest;
use App\Http\Requests\FamilyCreateRequest;
use App\Http\Requests\FamilyEditRequest;
use App\Http\Requests\FamilyProfileGetRequest;
use App\Http\Requests\FamilyStatusRequest;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\FamilyTrait;
use App\Interfaces\FamilyInterface;
use App\Jobs\DeleteMemberJob;
use App\Jobs\FamilyCreateJob;
use App\Jobs\FamilyRequestDeleteJob;
use App\Jobs\RequestNotificationDeleteJob;
use App\Notification;
use App\Relation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;
use Mail;

class FamilyController extends Controller implements FamilyInterface
{

    use CommonTrait, FamilyTrait;

    /*
     * Function to fetch family types
     */
    public function getFamilyRelations(Request $request)
    {
        $familyTypes = Relation::where('status', 1)->get();
        if ($familyTypes) {
            $data['status'] = 200;
            $data['message'] = 'Data Retrieved';
            $data['data'] = $familyTypes;
            return Response::json($data);
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching data';
            return Response::json($data);
        }
    }

    /*
     * Function to fetch family types
     */
    public function getFamilyTypes(Request $request)
    {
        $familyTypes = FamilyType::where('status', 1)->get();
        if ($familyTypes) {
            $data['status'] = 200;
            $data['message'] = 'Data Retrieved';
            $data['data'] = $familyTypes;
            return Response::json($data);
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching data';
            return Response::json($data);
        }
    }
    /*
     * Function to create family profile
     */
    public function createFamilyProfile(FamilyCreateRequest $request)
    {
        $requested_data = $request->all();
        // check is user already exist on platform
        $isUserExist = User::select('id')->where('email', $requested_data['email'])->first();

        if ($isUserExist) {
            // check is user already member with this user
            $isAlreadyMember = $this->checkAlreadyMember($requested_data['email']);
            if ($isAlreadyMember != 0) {
                $data['status'] = 400;
                $data['message'] = 'You are already member with this person.';
                return Response::json($data);
            }
            // fetch the count of members allowed in selected family type
            $allowedMembers = $this->getAllowedMembers($requested_data['family_id'], $requested_data);

            // check user family members added for the selected family type
            $usersMembers = $this->getUsersAddedMembers($requested_data['family_id']);

            if ($usersMembers < $allowedMembers) {
                $data = [];
                $data['invited_by'] = Auth::user()->id;
                $data['user_id'] = $isUserExist->id;
                $data['name'] = $requested_data['name'];
                $data['email'] = $requested_data['email'];
                $data['dob'] = $requested_data['dob'];
                $data['family_id'] = $requested_data['family_id'];
                $data['relation'] = $requested_data['relation'];
                $data['location'] = $requested_data['location'];
                $data['lng'] = $requested_data['lng'];
                $data['lat'] = $requested_data['lat'];
                $data['code'] = 'WD' . '-' . $this->imageDynamicName();
                $data['status'] = 0;
                $data['created_at'] = time();

                $insertFamily = FamilyMember::create($data);
                if ($insertFamily) {

                    // insert in notification table
                    $notification = [];
                    $notification['sender_id'] = Auth::user()->id;
                    $notification['receiver_id'] = $isUserExist->id;
                    $notification['notification_type'] = 'member_request';
                    $notification['email'] = '';
                    $notification['family_id'] = $requested_data['family_id'];
                    $notification['is_read'] = 0;
                    $notification['status'] = 1;
                    $notification['created_at'] = time();
                    $notification['updated_at'] = time();

                    Notification::create($notification);

                    // get mail data as per ids
                    $getMailData = FamilyMember::with(['senderDetail' => function ($q) {
                        $q->select('name', 'email', 'id');
                    },
                        'receiverDetail' => function ($q) {
                            $q->select('name', 'email', 'id');
                        }, 'familyTypeDetail'])->where('id', $insertFamily->id)->first();

                    // send mail
                    //if ($getMailData->familyTypeDetail['slug'] != 'member') {
                    FamilyCreateJob::dispatch($getMailData)->delay(now()->addSeconds(5));
                    //}
                    $datas['message'] = 'Request has been sent to the user.';
                    $datas['status'] = 200;
                    return Response::json($datas);
                } else {
                    $datas['message'] = 'Error';
                    $datas['status'] = 400;
                    return Response::json($datas);
                }
            } else {
                $data['status'] = 400;
                $data['message'] = 'Members limit reached, not allowed to add more members.';
                return Response::json($data);
            }
        } else {
            // check already sent member request
            $isAlreadyMember = $this->checkAlreadyMember($requested_data['email']);
            if ($isAlreadyMember != 0) {
                $data['status'] = 400;
                $data['message'] = 'You have already sent member request to this person.';
                return Response::json($data);
            }

            // fetch the count of members allowed in selected family type
            $allowedMembers = $this->getAllowedMembers($requested_data['family_id'], $requested_data);
            // check user family members added for the selected family type
            $usersMembers = $this->getUsersAddedMembers($requested_data['family_id']);

            if ($usersMembers < $allowedMembers) {
                $family_member_create = [];
                $family_member_create['invited_by'] = Auth::user()->id;
                $family_member_create['user_id'] = null;
                $family_member_create['name'] = $requested_data['name'];
                $family_member_create['email'] = $requested_data['email'];
                $family_member_create['dob'] = $requested_data['dob'];
                $family_member_create['family_id'] = $requested_data['family_id'];
                $family_member_create['relation'] = $requested_data['relation'];
                $family_member_create['location'] = $requested_data['location'];
                $family_member_create['lng'] = $requested_data['lng'];
                $family_member_create['lat'] = $requested_data['lat'];
                $family_member_create['code'] = 'WD' . '-' . $this->imageDynamicName();
                $family_member_create['status'] = 0;
                $family_member_create['created_at'] = time();
                $insertFamily = FamilyMember::create($family_member_create);
                if ($insertFamily) {
                    // insert in notification table
                    $notification = [];
                    $notification['sender_id'] = Auth::user()->id;
                    $notification['receiver_id'] = null;
                    $notification['notification_type'] = 'member_request';
                    $notification['email'] = $requested_data['email'];
                    $notification['family_id'] = $requested_data['family_id'];
                    $notification['is_read'] = 0;
                    $notification['status'] = 1;
                    $notification['created_at'] = time();
                    $notification['updated_at'] = time();

                    Notification::create($notification);

                    // get mail data as per ids
                    $getMailData = FamilyMember::with(['senderDetail' => function ($q) {
                        $q->select('name', 'email', 'id');
                    }, 'familyTypeDetail'])->where('id', $insertFamily->id)->first();

                    // send mail
                    // if ($getMailData->familyTypeDetail['slug'] != 'member') {
                    FamilyCreateJob::dispatch($getMailData)->delay(now()->addSeconds(5));
                    //}
                    $datas['message'] = 'Request has been sent to the user.';
                    $datas['status'] = 200;
                    return Response::json($datas);
                } else {
                    $datas['message'] = 'Error';
                    $datas['status'] = 400;
                    return Response::json($datas);
                }
            } else {
                $data['status'] = 400;
                $data['message'] = 'Members limit reached, not allowed to add more members.';
                return Response::json($data);
            }
        }
    }

    /*
     * Function to change the status of family request sent to user
     * status 1 - Accept, 2 - Reject
     */
    public function changeFamilyRequestStatus(FamilyStatusRequest $request)
    {
        $requested_data = $request->all();
        // dd($requested_data);
        // fetch updated record
        $record = FamilyMember::where('invited_by', $requested_data['invitedby_id'])
            ->where('user_id', Auth::user()->id)->first();

        if ($record) {
            // fetch the count of members allowed in selected family type
            $allowedMembers = $this->getAllowedMembers($record->family_id, $requested_data);

            // check user family members added for the selected family type
            $usersMembers = $this->getInvitorAddedMembers($record->family_id, $requested_data['invitedby_id']);

            if (($allowedMembers == $usersMembers) || ($allowedMembers < $usersMembers)) {
                $datas['message'] = 'Your request is no longer valid.';
                $datas['status'] = 200;
                return Response::json($datas);
            }
        } else {
            $datas['message'] = 'Your request is no longer valid.';
            $datas['status'] = 200;
            return Response::json($datas);
        }
        $changeStatus = FamilyMember::where('invited_by', $requested_data['invitedby_id'])
            ->where('user_id', Auth::user()->id)
            ->update(['status' => $requested_data['status']]);

        if ($changeStatus) {
            // check the requested members count for the invitor complete or not, if completed then delete the remaning request
            if ($requested_data['status'] == 1) {
                // fetch updated record
                $record = FamilyMember::where('invited_by', $requested_data['invitedby_id'])
                    ->where('user_id', Auth::user()->id)->first();

                // fetch the count of members allowed in selected family type
                $allowedMembers = $this->getAllowedMembers($record->family_id, $requested_data);

                // check user family members added for the selected family type
                $usersMembers = $this->getInvitorAddedMembers($record->family_id, $requested_data['invitedby_id']);

                if ($allowedMembers == $usersMembers) {
                    $requested_data['family_id'] = $record->family_id;
                    FamilyRequestDeleteJob::dispatch($requested_data)->delay(now()->addSeconds(5));
                    RequestNotificationDeleteJob::dispatch($requested_data)->delay(now()->addSeconds(5));
                }

                $datas['message'] = 'Request accepted successfully';
                $datas['status'] = 200;
                return Response::json($datas);
            } else {
                $datas['message'] = 'Request rejected successfully';
                $datas['status'] = 200;
                return Response::json($datas);
            }
        } else {
            $datas['message'] = 'Your request is no longer valid';
            $datas['status'] = 400;
            return Response::json($datas);
        }
    }

    /*
     * function to get family member profile
     */
    public function getFamilyProfile(FamilyProfileGetRequest $request)
    {
        $requested_data = $request->all();

        $familyProfile = FamilyMember::with(['relationData'])->where('id', $requested_data['id'])->first();
        if ($familyProfile) {
            $data['status'] = 200;
            $data['message'] = 'Family user profile fetched successfully.';
            $data['data'] = $familyProfile;
            return Response::json($data);
        } else {
            $data['message'] = 'Error';
            $data['status'] = 400;
            $data['data'] = [];
            return Response::json($data);
        }
    }

    /*
     * function to edit family member profile
     */
    public function editFamilyProfile(FamilyEditRequest $request)
    {
        $requested_data = $request->all();

        $familyProfile = FamilyMember::where('id', $requested_data['id'])->update(['name' => $requested_data['name'],
            'dob' => $requested_data['dob'],
            'relation' => $requested_data['relation'],
            'location' => $requested_data['location'],
            'lng' => $requested_data['lng'],
            'lat' => $requested_data['lat'],
            'updated_at' => time(),
        ]);

        if ($familyProfile) {
            $data['status'] = 200;
            $data['message'] = 'Family profile updated successfully.';
            $data['data'] = $familyProfile;
            return Response::json($data);
        } else {
            $data['message'] = 'Error in updating family profile.';
            $data['status'] = 400;
            return Response::json($data);
        }

    }

    /*
     * function to get family member profile
     */
    public function getMyFamilyMembers(Request $request)
    {
        $requested_data = $request->all();
        $search = !empty($requested_data["q"]) ? $requested_data["q"] : '';

        $familyProfile = FamilyMember::where(['invited_by' => $requested_data["data"]["id"], "status" => 1])
            ->select('id', 'invited_by', 'user_id', 'name', 'email', 'family_id', 'relation', 'status', 'created_at')
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
            }])
            ->orderBy('created_at', 'desc');

        if ($requested_data && !empty($requested_data["pagination"]) && $requested_data["pagination"] == 'false') {
            $familyProfile = $familyProfile->where('user_id', '!=', $requested_data["user_id"])->get();
        } else {
            $familyProfile = $familyProfile->paginate(config('variable.PER_PAGE'));
        }

        if ($familyProfile) {
            return Response::json(["status" => 200, 'message' => 'Family users fetched successfully.', 'data' => $familyProfile]);
        } else {
            return Response::json(["status" => 400, 'message' => 'Error', 'data' => []]);
        }
    }

    public function getUserFolderWithPermissions(Request $request)
    {
        $requested_data = $request->all();
        $user_id = FamilyMember::where('id', $requested_data["id"])->select('user_id')->first()->toArray();

        $response = FolderPermission::
            selectRaw(
            'DISTINCT(folder_id)'
        )
            ->join('folders', 'folders.id', '=', 'folder_permissions.folder_id')
            ->where('folders.user_id', $requested_data["data"]["id"])
            ->where('folder_permissions.user_id', $user_id)
            ->where('folder_permissions.status', 1)
            ->orderBy('folder_permissions.user_id', 'asc')
            ->get()->toArray();

        // My permission array
        $collection = collect($response)->map(function ($name, $key) {
            return $name[$key] = $name["folder_id"];
        });
        $ids = $collection->toArray();
        if ($ids) {
            $res = FolderPermission::wherehas('permission', function ($q) {
                $q->where('status', 1);
            })->with(['permission' => function ($q) {
                $q->select('id', 'name', 'slug', 'status')->where('status', 1);
            }, 'folder' => function ($q) {
                $q->select('id', 'user_id', 'name', 'slug');
            }])->whereIn('folder_id', $ids)->where(["user_id" => $user_id["user_id"]])->where('status', 1)->get()->toArray();

            $array = [];
            $i = 0;
            $prev_folder_id = '';
            if (isset($res) && !empty($res)) {
                foreach ($res as $key => $value) {
                    if ($value["folder_id"] == $prev_folder_id) {
                        $array[$i - 1]["folder_id"] = $value["folder_id"];
                        $array[$i - 1]["name"] = $value["folder"]["name"];
                        $array[$i - 1]["permission"][] = $value["permission"][0]["name"];
                    } else {
                        $array[$i]["folder_id"] = $value["folder_id"];
                        $array[$i]["name"] = $value["folder"]["name"];
                        $array[$i]["permission"][] = $value["permission"][0]["name"];
                        $prev_folder_id = $value["folder_id"];
                        $i++;
                    }
                }
            } else {
                $array = [];
            }
            return Response::json(["status" => 200, 'message' => 'Member deleted successfully.', 'data' => $array]);
        } else {
            return Response::json(["status" => 400, 'message' => 'No data found', 'data' => $array]);
        }

    }
    public function deleteMember(DeleteMemberRequest $request)
    {
        $requested_data = $request->all();
        $user_id = FamilyMember::where('id', $requested_data["id"])->select('user_id')->first()->toArray();

        //get user_id from the
        $response = FolderPermission::
            selectRaw(
            'DISTINCT(folder_id)'
        )
            ->join('folders', 'folders.id', '=', 'folder_permissions.folder_id')
            ->where('folders.user_id', $requested_data["data"]["id"])
            ->where('folder_permissions.user_id', $user_id)
            ->get()->toArray();

        // My permission array
        $collection = collect($response)->map(function ($name, $key) {
            return $name[$key] = $name["folder_id"];
        });
        $ids = $collection->toArray();
        // FamilyMember::where('invited_by', $requested_data["data"]["id"])->where('user_id', $user_id["user_id"])->update(["status" => 0]);
        DeleteMemberJob::dispatch([$ids, $user_id["user_id"]])->delay(now()->addSeconds(3));
        if ($response) {
            FamilyMember::where('invited_by', $requested_data["data"]["id"])->where('user_id', $user_id["user_id"])->delete();
            return Response::json(["status" => 200, 'message' => 'Member deleted successfully']);
        } else {
            return Response::json(["status" => 400, 'message' => 'There was an error while deleting member data']);
        }
    }

    public function swapUser(Request $request)
    {
        $requested_data = $request->all();
        
        // Check user is switching from which role to which role
        $from = FamilyMember::where('invited_by', $requested_data["data"]["id"])->where('user_id', $requested_data['from'])->first()->toArray();
        $to = FamilyMember::where('invited_by', $requested_data["data"]["id"])->where('user_id', $requested_data['to'])->first()->toArray();
        // print_r($from);
        // print_r($to);
        // If selected role is member then generate pin for this user and send email
        $response = FamilyType::where([
            'id' => $to["family_id"],
            'slug' => 'member',
        ])->select('id', 'slug')->first();
           
        if(isset($response) && !empty($response)){
            // FamilyMember::where('id', $from['id'])->update(['code' => '']);
            //FamilyMember::where('id', $from['id'])->update(['code' => time() ]);
            $getMailData = FamilyMember::with(['senderDetail' => function ($q) {
                $q->select('name', 'email', 'id');
            },'receiverDetail' => function ($q) {
                $q->select('name', 'email', 'id');
            }, 'familyTypeDetail'])->where('id', $from['id'])->first();
            //send email to the primary or gurantee 
            $primary_email = $getMailData->receiverDetail->email;
            $creator_name = $getMailData->senderDetail->name;      
            //print_r($getMailData);die;
            Mail::send('emails.family.send_pin', ["data" => $getMailData], function ($message) use ($primary_email, $creator_name) {
                $message->from(config('variable.ADMIN_EMAIL'), config('variable.SITE_NAME'));
                $message->to($primary_email, config('variable.SITE_NAME'))->subject(config('variable.SITE_NAME') . ' : ' . $creator_name . ' has changed your role');
            });
            
        }
       //die('ends');
        $swap_from = FamilyMember::where('id', $from['id'])->update(['family_id' => $to["family_id"]]);
        $swap_to = FamilyMember::where('id', $to['id'])->update(['family_id' => $from["family_id"]]);


        $success = Notification::where('invited_by', $requested_data["data"]["id"])->where('notification_type', 'role_swap')->delete();
        if ($success) {
            /* Notofication for */
            $this->saveNotifications([
                'receiver_id' => $from["user_id"],
                'invited_by' => $requested_data["data"]["id"],
                'family_id' => $to["family_id"],
                'status' => 1,
                'notification_type' => 'role_swap',
                'is_read' => 0,
                'created_at' => time(),
            ]);

            /* Notofication for */
            $this->saveNotifications([
                'receiver_id' => $to["user_id"],
                'invited_by' => $requested_data["data"]["id"],
                'family_id' => $from["family_id"],
                'status' => 1,
                'notification_type' => 'role_swap',
                'is_read' => 0,
                'created_at' => time(),
            ]);
        }
        if ($swap_from && $swap_to) {
            return Response::json(["status" => 200, 'message' => 'Role swapped successfully']);
        } else {
            return Response::json(["status" => 400, 'message' => 'There was an error while swapping role']);
        }
    }
}
