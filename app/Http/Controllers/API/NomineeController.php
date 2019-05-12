<?php

namespace App\Http\Controllers\API;

use App\FamilyMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeclareUserRequest;
use App\Http\Requests\ForgotPinRequest;
use App\Http\Traits\CommonTrait;
use App\Interfaces\NomineeInterface;
use App\Jobs\ForgotPinJob;
use App\User;
use Config;
use Illuminate\Http\Request;
use Mail;
use Response;

class NomineeController extends Controller implements NomineeInterface
{
    //
    use CommonTrait;
    public function getNominee(Request $request)
    {
        $requested_data = $request->all();
        $query = FamilyMember::whereHas('familyTypeDetail', function ($q) {
            $q->whereIn('slug', ["primary", "guarantee"]);
        })->with(['familyTypeDetail' => function ($q) {
            $q->select('id', 'slug', 'name', 'status');
        }, 'senderDetail' => function ($q) {
            $q->select('id', 'slug', 'name', 'status', 'temp_primary_declaration', 'temp_guarantee_declaration');
        }, 'receiverDetail' => function ($q) {
            $q->select('id', 'slug', 'name', 'status');
        }, 'relationData' => function ($q) {
            $q->select('id', 'slug', 'name', 'status');
        }]);

        if (isset($requested_data['search']) && !empty($requested_data['search'])) {
            $query = $query->whereHas('senderDetail', function ($q) use ($requested_data) {
                $q->whereRaw("( REPLACE(name,' ','')  LIKE '%" . str_replace(' ', '', $requested_data['search']) . "%')");
            });
        }
        $query = $query->select('id', 'invited_by', 'user_id', 'family_id', 'relation', 'status', 'name', 'email', 'dob')
            ->where(["user_id" => $requested_data["data"]["id"], "status" => 1])
            ->orderBy('created_at', 'desc')
            ->paginate(config("variable.PER_PAGE"));

        if ($query) {
            $data['status'] = 200;
            $data['message'] = 'User data fetched successfully';
            $data['data'] = $query;
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching data';
            $data['data'] = [];
        }
        return Response::json($data);
    }

    public function declareUser(DeclareUserRequest $request)
    {
        $requested_data = $request->all();
        //get who is declaring
        $query = FamilyMember::with(['familyTypeDetail' => function ($q) {
            $q->select('id', 'name', 'slug');
        }, 'senderDetail' => function ($q) {
            $q->select('id', 'slug', 'name', 'email', 'status', 'temp_primary_declaration', 'temp_guarantee_declaration');
        }, 'receiverDetail' => function ($q) {
            $q->select('id', 'slug', 'email', 'name', 'status');
        }, 'relationData' => function ($q) {
            $q->select('id', 'slug', 'name', 'status');
        }])->where('id', $requested_data['row_id'])->select('id', 'invited_by', 'user_id', 'family_id', 'code', 'relation')->first();

        if ($query->familyTypeDetail->slug === 'primary') {

            $updated = User::where('id', $requested_data['invited_by'])->update([
                'temp_primary_declaration' => 1,
            ]);

            $creator_name = $query->senderDetail->name;
            $receiver_name = $query->receiverDetail->name;
            //Send email to creator of the user
            $email = $query->senderDetail->email;
            Mail::send('emails.family.declare_user', ["data" => $query, "type" => 'primary_sender'], function ($message) use ($email, $receiver_name) {
                $message->from(config('variable.ADMIN_EMAIL'), config('variable.SITE_NAME'));
                $message->to($email, config('variable.SITE_NAME'))->subject(config('variable.SITE_NAME') . ' : You have been declared as dead by primary user ' . $receiver_name);
            });

            $guarantee = FamilyMember::where(["invited_by" => $requested_data['invited_by']])
                ->whereHas('familyTypeDetail', function ($q) {
                    $q->where('slug', "guarantee");
                })
                ->with(['familyTypeDetail' => function ($q) {
                    $q->select('id', 'name', 'slug');
                }, 'receiverDetail' => function ($q) {
                    $q->select('id', 'slug', 'name', 'email', 'status');
                }, 'senderDetail' => function ($q) {
                    $q->select('id', 'slug', 'name', 'email', 'status');
                }])->select('id', 'invited_by', 'user_id', 'family_id', 'code', 'relation')->first();

            // Send email to the guarantee user
            $guarantee_email = $guarantee->receiverDetail->email;
            Mail::send('emails.family.declare_user', ["data" => $guarantee, "type" => 'primary_receiver'], function ($message) use ($guarantee_email, $creator_name) {
                $message->from(config('variable.ADMIN_EMAIL'), config('variable.SITE_NAME'));
                $message->to($guarantee_email, config('variable.SITE_NAME'))->subject(config('variable.SITE_NAME') . ' : ' . $creator_name . ' has been declared as dead by the primary member');
            });

            if (count(Mail::failures()) > 0) {
                $data['message'] = 'There is an error while sending email';
                $data['status'] = 400;
                return Response::json($data);
            } else {
                /**************NOTIFICATION FOR PARENT USER******************/
                $this->saveNotifications([
                    'sender_id' => $query->receiverDetail->id,
                    'receiver_id' => $query->senderDetail->id,
                    'notification_type' => 'primary_sender',
                    'status' => 1,
                    'is_read' => 0,
                    'created_at' => time(),
                ]);
                /*************NOTIFICATION FOR PARENT USER**********/

                /**************NOTIFICATION FOR PARENT USER******************/
                $this->saveNotifications([
                    'sender_id' => $query->receiverDetail->id,
                    'receiver_id' => $guarantee->receiverDetail->id,
                    'invited_by' => $requested_data['invited_by'],
                    'notification_type' => 'primary_receiver',
                    'status' => 1,
                    'is_read' => 0,
                    'created_at' => time(),
                ]);
                /*************NOTIFICATION FOR PARENT USER**********/

                $data['message'] = 'Record updated successfully';
                $data['status'] = 200;
                return Response::json($data);
            }
        }

        if ($query->familyTypeDetail->slug === 'guarantee') {
            $updated = User::where('id', $requested_data['invited_by'])->update([
                'temp_guarantee_declaration' => 1,
                'temp_declaration_date' => time(),
            ]);

            $creator_name = $query->senderDetail->name;
            $receiver_name = $query->receiverDetail->name;
            //Send email to creator of the user
            $email = $query->senderDetail->email;
            Mail::send('emails.family.declare_user', ["data" => $query, "type" => 'guarantee_sender'], function ($message) use ($email, $receiver_name) {
                $message->from(config('variable.ADMIN_EMAIL'), config('variable.SITE_NAME'));
                $message->to($email, config('variable.SITE_NAME'))->subject(config('variable.SITE_NAME') . ' : You have been declared as dead by guarantee user ' . $receiver_name);
            });

            // Send email to the primary user

            $primary = FamilyMember::where(["invited_by" => $requested_data['invited_by']])
                ->whereHas('familyTypeDetail', function ($q) {
                    $q->where('slug', "primary");
                })
                ->with(['familyTypeDetail' => function ($q) {
                    $q->select('id', 'name', 'slug');
                }, 'receiverDetail' => function ($q) {
                    $q->select('id', 'slug', 'name', 'email', 'status');
                }, 'senderDetail' => function ($q) {
                    $q->select('id', 'slug', 'name', 'email', 'status');
                }])->select('id', 'invited_by', 'user_id', 'family_id', 'code', 'relation')->first();

            // Send email to the guarantee user
            $primary_email = $primary->receiverDetail->email;
            Mail::send('emails.family.declare_user', ["data" => $primary, "type" => 'guarantee_receiver'], function ($message) use ($primary_email, $creator_name) {
                $message->from(config('variable.ADMIN_EMAIL'), config('variable.SITE_NAME'));
                $message->to($primary_email, config('variable.SITE_NAME'))->subject(config('variable.SITE_NAME') . ' : ' . $creator_name . ' has been declared as dead by the guarantee member');
            });
            if (count(Mail::failures()) > 0) {
                $data['message'] = 'There is an error while sending email';
                $data['status'] = 400;
                return Response::json($data);
            } else {
                /**************NOTIFICATION FOR PARENT USER******************/
                $this->saveNotifications([
                    'sender_id' => $query->receiverDetail->id,
                    'receiver_id' => $query->senderDetail->id,
                    'notification_type' => 'guarantee_sender',
                    'status' => 1,
                    'is_read' => 0,
                    'created_at' => time(),
                ]);
                /*************NOTIFICATION FOR PARENT USER**********/
                
                 /**************NOTIFICATION FOR PARENT USER******************/
                 $this->saveNotifications([
                    'sender_id' => $query->receiverDetail->id,
                    'receiver_id' => $primary->receiverDetail->id,
                    'invited_by' => $requested_data['invited_by'],
                    'notification_type' => 'guarantee_receiver',
                    'status' => 1,
                    'is_read' => 0,
                    'created_at' => time(),
                ]);
                /*************NOTIFICATION FOR PARENT USER**********/


                $data['message'] = 'Record updated successfully';
                $data['status'] = 200;
                return Response::json($data);
            }
            return Response::json($data);

        }

    }

    public function forgotPin(ForgotPinRequest $request)
    {
        $requested_data = $request->all();

        $query = FamilyMember::with(['familyTypeDetail' => function ($q) {
            $q->select('id', 'slug', 'name', 'status');
        }, 'senderDetail' => function ($q) {
            $q->select('id', 'slug', 'name', 'status', 'temp_primary_declaration', 'temp_guarantee_declaration');
        }, 'receiverDetail' => function ($q) {
            $q->select('id', 'slug', 'name', 'status');
        }, 'relationData' => function ($q) {
            $q->select('id', 'slug', 'name', 'status');
        }])
            ->where(['id' => $requested_data['row_id'], 'email' => $requested_data['email'], 'dob' => $requested_data['dob']])
            ->whereRaw("( REPLACE(name,' ','') = '" . str_replace(' ', '', $requested_data['name']) . "')")
            ->select('id', 'invited_by', 'user_id', 'family_id', 'code', 'relation')->first();

        if (!empty($query->senderDetail) && isset($query->senderDetail)) {
            ForgotPinJob::dispatch($query)->delay(now()->addSeconds(5));
        }
        if ($query) {
            $data['status'] = 200;
            $data['message'] = 'Mail sent successfully';
            $data['data'] = [];
        } else {
            $data['status'] = 400;
            $data['message'] = 'No data found with this criteria';
            $data['data'] = [];
        }
        return Response::json($data);
    }
}
