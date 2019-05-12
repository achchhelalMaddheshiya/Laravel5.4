<?php
//redis-server
//sudo supervisorctl start all
namespace App\Http\Controllers\API;

use App\FamilyMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationReadRequest;
use App\Interfaces\NotificationInterface;
use App\Notification;
use Config;
use Illuminate\Http\Request;
use Response;

class NotificationController extends Controller implements NotificationInterface
{

    /*
     * Function to mark notifications as read
     */
    public function readNotification(NotificationReadRequest $request)
    {
        $requested_data = $request->all();

        $markRead = Notification::where('id', $requested_data['notification_id'])->update(['is_read' => 1]);
        if ($markRead) {
            $datas['message'] = 'Notification marked as read successfully';
            $datas['status'] = 200;
            return Response::json($datas);
        } else {
            $datas['message'] = 'Error';
            $datas['status'] = 400;
            return Response::json($datas);
        }
    }

    /*
     * Function to get all notification of logged in user
     */

    public function getNotifications(Request $request)
    {
        $requested_data = $request->all();
        // ->where('notification_type','!=','member_request')
        $res = Notification::where(function ($query) use ($requested_data) {
            $query->where('receiver_id', '=', $requested_data["data"]["id"])
                ->orWhere('sender_id', '=', $requested_data["data"]["id"]);
        })->select('id', 'notification_type', 'is_read', 'created_at', 'email', 'family_id', 'sender_id', 'folder_id', 'receiver_id', 'invited_by', 'status')
            ->with(['sender' => function ($q) {
                $q->select('id', 'name', 'image', 'slug')->where('status', 1);
            }, 'receiver' => function ($q) {
                $q->select('id', 'name', 'image', 'slug')->where('status', 1);
            }, 'familyType' => function ($q) {
                $q->select('id', 'name', 'slug')->where('status', 1);
            }, 'creator' => function ($q) {
                $q->select('id', 'name', 'image', 'slug')->where('status', 1);
            }, 'folder' => function ($q) {
                $q->select('id', 'user_id', 'name', 'slug');
            }, 'folder.creator' => function ($q) {
                $q->select('id', 'name', 'slug', 'image', 'primary_declaration', 'guarantee_declaration', 'status');
            }])
            ->orderBy('created_at', 'desc')->paginate(config('variable.PER_PAGE'));

        $response = ($res) ? $res->toArray() : $res;

        if (!empty($response) && isset($response)) {
            foreach ($response["data"] as $key => $value) {
                $response["data"][$key] = $value;
                //Create reponse based on type
                if ($value['sender_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'member_request' && $value['status'] == 1) {
                    $response["data"][$key]['message'] = $value['receiver']['name'] . ' has accepted your request to be a ' . ucfirst($value['family_type']['slug']) . ' user';
                    $response["data"][$key]['receiver_image'] = $value['receiver']['image'];
                }

                if ($value['receiver_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'member_request' && $value['status'] == 1) {
                    $response["data"][$key]['message'] = $value['sender']['name'] . ' has sent a request to be a ' . ucfirst($value['family_type']['slug']) . ' user';
                    $response["data"][$key]['receiver_image'] = $value['sender']['image'];
                }

                // Parent user as dead by primary
                if ($value['sender_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'primary_sender') {
                    $response["data"][$key]['message'] = 'You as primary have declared ' . $value['receiver']['name'] . ' as dead  ';
                    $response["data"][$key]['receiver_image'] = $value['sender']['image'];
                }
                // Parent as dead
                if ($value['receiver_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'primary_sender') {
                    $response["data"][$key]['message'] = 'You have been declared as dead by primary user ' . $value['sender']['name'];
                    $response["data"][$key]['receiver_image'] = $value['sender']['image'];
                }

                ///Parent declere as dead by guarantee
                if ($value['receiver_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'primary_receiver') {
                    $response["data"][$key]['message'] = $value['sender']['name'] . ' as primary has declared ' . $value['creator']["name"] . ' as dead';
                    $response["data"][$key]['receiver_image'] = $value['sender']['image'];
                }

                // Parent user as dead by guarantee
                if ($value['sender_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'guarantee_sender') {
                    $response["data"][$key]['message'] = 'You as primary have declared ' . $value['receiver']['name'] . ' as dead ';
                    $response["data"][$key]['receiver_image'] = $value['receiver']['image'];
                }
                // Parent as dead
                if ($value['receiver_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'guarantee_sender') {
                    $response["data"][$key]['message'] = 'You have been declared as dead by guarantee user ' . $value['sender']['name'];
                    $response["data"][$key]['receiver_image'] = $value['sender']['image'];
                }

                ///Parent declere as dead by guarantee
                if ($value['receiver_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'guarantee_receiver') {
                    $response["data"][$key]['message'] = $value['sender']['name'] . ' as guarantee has declared ' . $value['creator']["name"] . ' as dead';
                    $response["data"][$key]['receiver_image'] = $value['sender']['image'];
                }

                if ($value['receiver_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'folder_assigned') {
                    $response["data"][$key]['message'] = $value['folder']['creator']['name'] . ' has assigned you a folder' . $value['folder']["name"];
                    $response["data"][$key]['receiver_image'] = $value['folder']['creator']['image'];
                }

                if ($value['receiver_id'] == $requested_data["data"]["id"] && $value['notification_type'] == 'role_swap') {
                    $response["data"][$key]['message'] = $value['creator']["name"] . ' has changed your role to '.$value["family_type"]["slug"];
                    $response["data"][$key]['receiver_image'] = $value['creator']["name"];
                }
            }
        }

        if ($response) {
            $datas['data'] = $response;
            $datas['message'] = 'Notification retrieved successfully';
            $datas['status'] = 200;
            return Response::json($datas);
        } else {
            $datas['message'] = 'Error';
            $datas['status'] = 400;
            $datas['data'] = [];
            return Response::json($datas);
        }
    }
    /*
    Function to get requests to that user can acceopt
     */
    public function getMyRequests(Request $request)
    {
        $requested_data = $request->all();
        $res = FamilyMember::where(['user_id' => $requested_data["data"]["id"], "status" => 0])->select('id', 'invited_by', 'user_id', 'name', 'email', 'family_id', 'relation', 'status', 'created_at')
            ->with(['senderDetail' => function ($q) {
                $q->select('id', 'name', 'image', 'slug')->where('status', 1);
            }, 'familyTypeDetail' => function ($q) {
                $q->select('id', 'name', 'slug')->where('status', 1);
            }, 'relationData' => function ($q) {
                $q->select('id', 'name', 'slug')->where('status', 1);
            }])
        // config('variable.PER_PAGE')
            ->orderBy('created_at', 'desc')->paginate(10000);

        if ($res) {
            $datas['data'] = $res;
            $datas['message'] = 'Requests retrieved successfully';
            $datas['status'] = 200;
            return Response::json($datas);
        } else {
            $datas['message'] = 'Error';
            $datas['status'] = 400;
            $datas['data'] = [];
            return Response::json($datas);
        }
    }
}
