<?php
//redis-server
//sudo supervisorctl start all
namespace App\Http\Controllers\API;

use App\FamilyMember;
use App\Folder;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\UserChangePasswordRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserProfileImageRequest;
use App\Http\Requests\UserProfileRequest;
use App\Http\Requests\UserResendVerifyRequest;
use App\Http\Requests\UserResetPasswordRequest;
use App\Http\Requests\UserSignupRequest;
use App\Http\Requests\UserVerifyRequest;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\UserTrait;
use App\Interfaces\UserInterface;

// Load Model
use App\Jobs\SignupJob;
use App\Notification;
use App\Package;
use App\Role;
use App\User;
use App\UserPackage;
use Carbon\Carbon;
use Config;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Image;
use Lcobucci\JWT\Parser;
use Mail;
use Response;

class UsersController extends Controller implements UserInterface
{
    use CommonTrait, UserTrait;
    public function validateForgotPasswordExpiry(Request $request)
    {

        $data = [];
        $requested_data = $request->all();
        $response = User::where('forgot_password_token', $requested_data['token'])->first();
        if ($response && $response->forgot_token_expiry != '') {
            $from = Carbon::create(date('Y', $response->forgot_token_expiry), date('m', $response->forgot_token_expiry), date('d', $response->forgot_token_expiry), date('h', $response->forgot_token_expiry), date('i', $response->forgot_token_expiry), date('s', $response->forgot_token_expiry));
            $to = Carbon::now();
            if ($to->diffInSeconds($from) > 86400) {
                $data['message'] = 'This link has been expired';
                $data['status'] = 400;
            } else {
                $data['status'] = 200;
                $data['message'] = '';
            }
        } else {
            $data['message'] = 'This link has been expired';
            $data['status'] = 400;
        }
        return Response::json($data);
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function signUp(UserSignupRequest $request)
    {
        $requested_data = $request->all();
        $array['role_id'] = Role::where('name', 'user')->first()->id;
        $array['password'] = bcrypt($requested_data['password']);
        $array['email'] = $requested_data['email'];
        $array['verify_token'] = $this->getverificationCode();
        $array['created_at'] = time();
        $user = User::create($array);
        if ($user) {
            //Assign Free package to user
            UserPackage::create([
                'user_id' => $user->id,
                'package_id' => Package::where(['subscription_days' => 0, 'amount' => 0])->first()->id,
                'type' => 1,
                'status' => 1,
                'created_at' => time(),
            ]);

            // check email in family members table
            $checkInFamilyMember = FamilyMember::where('email', $requested_data['email'])->update(['user_id' => $user->id]);

            // check in notification table for signup user email
            $checkInNotification = Notification::where('email', $requested_data['email'])->update(['receiver_id' => $user->id, 'email' => '']);

            SignupJob::dispatch($user)->delay(now()->addSeconds(3));
            $data['message'] = 'Your account has been successfully registered with us, Please verify account your account';
            $data['status'] = 200;
            return Response::json($data);
        } else {
            $data['message'] = 'Error';
            $data['status'] = 400;
            return Response::json($data);
        }
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(UserLoginRequest $request)
    {
        $requested_data = $request->all();
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')], request('remember_me'))) {
            $user = Auth::user();

            switch ($user->status) {
                case 0:
                    Auth::logout();
                    $data['message'] = 'Your account is not verified, please verify';
                    $data['status'] = 400;
                    $data['user_unverified'] = true;
                    $data['email'] = request('email');
                    return Response::json($data);
                    break;
                case 3:
                    Auth::logout();
                    $data['status'] = 400;
                    $data['message'] = 'Your account has been blocked by the admin, please contact admin';
                    return Response::json($data);
                    break;
            }
           /* if ($user->primary_declaration == 1 && $user->guarantee_declaration == 1) {
                $data['status'] = 400;
                $data['message'] = 'Sorry your account has been put on hold by the Admin as your primary and guarantee, both declared you as passed away. If you are still alive please contact Admin';
                return Response::json($data);
            }*/
            $remember_me = isset($requested_data['remember_me']) ? $requested_data['remember_me'] : false;
            return Response::json([
                'status' => 200,
                'role_id' => $user->role_id,
                'profile_status' => $user->status,
                'remember_me' => $remember_me,
                'name' => $user->name,
            ])->header('access_token', $user->createToken(env("APP_NAME"))->accessToken);
        } else {
            $data['message'] = 'Invalid email/password';
            $data['status'] = 400;
            return Response::json($data);
        }
    }

    public function logout(Request $request)
    {
        $value = $request->bearerToken();
        $id = (new Parser())->parse($value)->getHeader('jti');
        $token = $request->user()->tokens->find($id);

        if ($token->revoke()) {
            $data['status'] = 200;
            $data['message'] = 'You are successfully logged out';
            return Response::json($data);
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error';
            return Response::json($data);
        }
    }

    /**
     *  Function : used to resend the verification link to user
     *  INput : email
     *  OUtput : Verification email will trigger with success message
     */
    public function resendVerification(UserResendVerifyRequest $request)
    {
        $requested_data = $request->all();
        $array['verify_token'] = $this->getverificationCode();
        $array['created_at'] = time();
        $user = User::where('email', $requested_data['email'])->update($array);
        if ($user) {
            $user = User::where('email', $requested_data['email'])->first();
            SignupJob::dispatch($user)->delay(now()->addSeconds(5));
            $data['message'] = 'A verification link has been sent to your registered email address, please verify';
            $data['status'] = 200;
            return Response::json($data);
        } else {
            $data['message'] = 'Error';
            $data['status'] = 400;
            return Response::json($data);
        }
    }

    /*
     * Function for verify link send on email
     * @param request parameters (verify_token)
     * @return response (status, message, success/failure)
     */

    public function verify(UserVerifyRequest $request)
    {
        $requested_data = $request->all();
        $expired_date = time() - 86400; //$date->format('Y-m-d H:i:s');
        $user = User::where('verify_token', '=', $requested_data['verify_token'])->where('created_at', '>=', $expired_date)->first();
        if (!empty($user)) {
            $user_id = $user->id;
            User::where('verify_token', $requested_data['verify_token']) // find your user by their verify_token
                ->update(array('verify_token' => '', 'status' => '1')); // update the record in the DB.
            $success = ['status' => 200, 'role_id' => $user->role_id];
            return Response::json(array('status' => 200, 'message' => 'Congratulations!! Your Account has been verified.'));
        } else {
            return Response::json(array('status' => 400, 'message' => 'link has been expired'));
        }
    }

    /*
     * Function to send a link forgot password
     * @param request parameters (verificatcontention code)
     * @return response (status, message, success/failure)
     */

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $requested_data = $request->all();
        $forgot_password_code = $this->getverificationCode();

        $check_user = User::where(['email' => $requested_data['email'], 'role_id' => Role::where('name', 'user')->first()->id])->first();

        if (!empty($check_user)) {
            User::where('email', $requested_data['email'])->update(array('forgot_password_token' => $forgot_password_code, 'forgot_token_expiry' => time() + 86400)); // update the record in the DB.

            $email = $requested_data['email'];
            $admin_email = Config::get('variable.ADMIN_EMAIL');
            $frontend_url = Config::get('variable.FRONTEND_URL');
            $name = $check_user->name;
            Mail::send('emails.users.forgot_password', [
                "data" => array("name" => $name,
                    "frontend_url" => $frontend_url,
                    "forgot_password" => $forgot_password_code,
                    "email" => $email,
                )], function ($message) use ($email, $admin_email) {
                $message->from($admin_email, config('variable.SITE_NAME'));
                $message->to(trim($email), config('variable.SITE_NAME'))->subject(config('variable.SITE_NAME') . ' : Forgot password');
            });
            if (count(Mail::failures()) > 0) {
                $data['message'] = 'There is an error while sending email';
                $data['status'] = 400;
                return Response::json($data);
            } else {
                $data['message'] = 'A link has been sent to your email address, please check your inbox';
                $data['status'] = 200;
                return Response::json($data);
            }
        } else {
            $data['message'] = 'Unauthorised action';
            $data['status'] = 200;
            return Response::json($data);
        }
    }

    /*
     * Function: function to reset password
     * Input:
     * Output:success/fail
     */

    public function resetPassword(UserResetPasswordRequest $request)
    {

        $requested_data = $request->all();
        $updated = User::where('forgot_password_token', $requested_data['forgot_password_token'])
            ->update(['password' => bcrypt($requested_data['password']), 'forgot_password_token' => ""]);
        if ($updated) {
            return Response::json(["status" => 200, "message" => "Password updated successfully"]);
        } else {
            return Response::json(["status" => 400, "message" => "There was an error while updating password"]);
        }
    }

    /*
     * Function: function to change login user password
     * Input: old_password , new_password , new_password_confirmation
     * Output:success/fail
     */

    public function changePassword(UserChangePasswordRequest $request)
    {
        $requested_data = $request->all();
        $old_password = $requested_data['old_password'];
        $hashedPassword = Auth::user()->password;

        if (Hash::check($old_password, $hashedPassword)) {
            $updated = User::find($requested_data['data']["id"])->update(['password' => Hash::make($requested_data['password'])]);
            if ($updated) {
                return Response::json(["status" => 200, "message" => "Password updated successfully"]);
            } else {
                return Response::json(["status" => 400, "message" => "There was an error while updating password"]);
            }
        } else {
            return Response::json(["status" => 400, "message" => "Wrong old password"]);
        }
    }

    /*
     * Function to fetch login user profile
     */
    public function getPersonalProfile(Request $request)
    {
        $requested_data = $request->all();
        if ($requested_data['data']['role']) {
            unset($requested_data['data']['role']);
        }

        if ($requested_data['data']['packages']) {
            unset($requested_data['data']['packages']);
        }
        if ($requested_data['data']) {
            $data['status'] = 200;
            $data['message'] = 'User profile fetched successfully';
            $data['data'] = $requested_data["data"];
            return Response::json($data);
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching profile';
            return Response::json($data);
        }
    }

    private function assignFolder($requested_data)
    {
        // Assign Default folder to user if already not assigned
        $is_assinged = Folder::where(['user_id' => $requested_data["data"]["id"], 'type' => 0, 'status' => 1])->count();

        if (!$is_assinged) {
            //Now Assigned Default folder to this user
            //First Create My Vault Folder as default
            $vault_created = Folder::create(['user_id' => $requested_data["data"]["id"], 'name' => 'My Personal Vault', 'type' => 0, 'parent_id' => 0, 'status' => 1, 'created_at' => time()]);
            if ($vault_created) {
                // Create Sub folder under this My Vault folder
                $default_folders = [
                    ['user_id' => $requested_data["data"]["id"], 'name' => 'Add Links', 'slug' => 'links', 'type' => 1, 'parent_id' => $vault_created->id, 'status' => 1, 'created_at' => time()],
                    ['user_id' => $requested_data["data"]["id"], 'name' => 'Locations', 'slug' => 'locations', 'type' => 1, 'parent_id' => $vault_created->id, 'status' => 1, 'created_at' => time()],
                    ['user_id' => $requested_data["data"]["id"], 'name' => 'Passwords', 'slug' => 'passwords', 'type' => 1, 'parent_id' => $vault_created->id, 'status' => 1, 'created_at' => time()],
                ];
                $other_folder_created = Folder::insert($default_folders);
                if ($other_folder_created) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /*
     * Function to update user profile
     */
    public function updatePersonalProfile(UserProfileRequest $request)
    {
        $requested_data = $request->all();

        $user = User::find(Auth::user()->id)
            ->update(
                ['name' => $requested_data['name']]
            );
        $folder_assigned = $this->assignFolder($requested_data);
        if (!$folder_assigned) {
            $data['status'] = 400;
            $data['message'] = 'Not able to assign vault';
            return Response::json($data);
        }
        if ($user) {
            $data['status'] = 200;
            $data['message'] = 'User profile updated successfully';
            return Response::json($data);
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in update';
            return Response::json($data);
        }
    }

    /*
     * Function to upload user profile image
     */
    public function uploadProfileImage(UserProfileImageRequest $request)
    {
        $requested_data = $request->all();

        $user = Auth::user();

        // check file extension
        $allowed = ['jpeg', 'png', 'jpg'];
        $filename = $_FILES['image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $data['status'] = 400;
            $data['message'] = 'Invalid file format. Allowed format : jpg,jpeg,png';
            return Response::json($data);
        }
        // check file size
        if ($_FILES['image']['size'] > 2097152) {
            $data['status'] = 400;
            $data['message'] = 'File too large. Max upload size 2MB';
            return Response::json($data);
        }

        //upload file
        $dynamic_name = time() . '-' . $this->imageDynamicName() . '.' . $ext;
        $image = $request->file('image')->storeAs('public/user', $dynamic_name);

        if ($image) {
            $image_name = explode('/', $image);
            $saved_Image = $this->userImageVersions($image_name[2]);
            if ($saved_Image) {
                // unlink file from directory
                if ($user->image != '' && $user->image != null) {
                    $previous_image_path = storage_path('app/public/user/') . $user->image;
                    $previous_image_path_thumb = storage_path('app/public/user/thumb/') . $user->image;

                    if (file_exists($previous_image_path)) {
                        unlink($previous_image_path);
                    }
                    if (file_exists($previous_image_path_thumb)) {
                        unlink($previous_image_path_thumb);
                    }
                }

                // save file name in user account
                $updateUser = User::where('id', $user->id)->update(['image' => $image_name[2]]);

                if ($updateUser) {
                    $server_url = Config::get('variable.SERVER_URL');
                    if (!empty($image_name[2]) && file_exists(storage_path() . '/app/public/user/thumb/' . $image_name[2])) {
                        $path = $server_url . '/storage/user/thumb/' . $image_name[2];
                    } else {
                        $path = $server_url . '/images/user-default.png';
                    }
                    $data['status'] = 200;
                    $data['message'] = 'Profile image uploaded successfully';
                    $data['image'] = $path;
                    return Response::json($data);
                } else {
                    $data['status'] = 400;
                    $data['message'] = 'Error in uploading file';
                    return Response::json($data);
                }
            } else {
                $data['status'] = 400;
                $data['message'] = 'Error in uploading file';
                return Response::json($data);
            }
        }
    }

    public function getPackages(Request $request)
    {
        $packages = Package::where('status', 1)->get();
        $requested_data = $request->all();

        if ($packages) {
            $data['status'] = 200;
            $data['message'] = 'Data Retrieved';
            $data['data'] = $packages;
            $data['user_packages'] = !empty($requested_data["data"]["packages"]) ? $requested_data["data"]["packages"] : '';
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching data';
            $data['data'] = [];
        }
        return Response::json($data);
    }

    public function getUser(Request $request)
    {
        $requested_data = $request->all();
        $user_data = $requested_data["data"];

        if ($requested_data["data"]["primary_declaration"] == 1 && $requested_data["data"]["guarantee_declaration"] == 1) {
            $data['status'] = 200;
            $data['is_expired'] = 102;
            $data['data'] = $user_data;
            $data['message'] = 'Access denied.You have been dead as dead by primary & guarantee';
            return Response::json($data);
        }
        if ($requested_data["data"]["packages"] == null) {
            $data['status'] = 200;
            $data['is_expired'] = 101;
            $data['message'] = 'You dont have any active subscription';
            $data['data'] = $user_data;
            return Response::json($data);
        }
        if ($requested_data["data"]["name"] == "") {
            $data['status'] = 403;
            $data['message'] = 'Complete your profile to proceed further';
            $data['data'] = [];
        } else if ($user_data) {
            $data['status'] = 200;
            $data['message'] = 'Data Retrieved';
            $data['data'] = $user_data;
        } else {
            $data['status'] = 400;
            $data['message'] = 'Error in fetching data';
            $data['data'] = [];
        }
        return Response::json($data);
    }

    public function changeEmail(Request $request)
    {
        $requested_data = $request->all();
        $verification_code = str_random(20);
        $obj_user = User::find($requested_data['data']['id']);
        $obj_user->secondary_email = $request['email'];
        $obj_user->email_verification_code = $verification_code;
        $obj_user->secondary_expiry = time() + 86400;
        $obj_user->save();

        //send verification mail to user
        //---------------------------------------------------------
        $data['verification_code'] = $verification_code;
        $data['name'] = $obj_user->name;
        $data['email'] = $request['email'];

        Mail::send('emails/users/confirm', $data, function ($message) use ($data) {
            $message->from(config('variable.ADMIN_EMAIL'), config('variable.SITE_NAME'));
            $message->subject("You have requested to change email address");
            $message->to($data['email']);
        });
        if (count(Mail::failures()) > 0) {
            $data['message'] = 'There is an error while sending email';
            $data['status'] = 400;

        } else {
            $data['message'] = 'A link has been sent to your email address, please check your inbox';
            $data['status'] = 200;
        }
        return Response::json($data);
    }

    public function verifyChangeEmail(Request $request)
    {
        $requested_data = $request->all();
        $expired_date = time() - 86400; //$date->format('Y-m-d H:i:s');
        $user = User::where('email_verification_code', '=', $requested_data['email_verification_code'])->where('secondary_expiry', '>=', $expired_date)->first();
        if (!empty($user)) {
            $user_id = $user->id;
            User::where('email_verification_code', $requested_data['email_verification_code']) // find your user by their verify_token
                ->update(array('email_verification_code' => '', 'email' => $user->secondary_email, 'secondary_email' => '', 'secondary_expiry' => '')); // update the record in the DB.
            return Response::json(array('status' => 200, 'message' => 'Congratulations!! Your Account has been verified.'));
        } else {
            return Response::json(array('status' => 400, 'message' => 'Link has been expired'));
        }
    }

}
