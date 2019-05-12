<?php
namespace App\Http\Traits;
use Image;
trait UserTrait {
    public function userImageVersions($name) {
        $main_dir = storage_path() . '/app/public/user';
        $thumb_dir = storage_path() . '/app/public/user/thumb';
        
        if (!file_exists($thumb_dir)) {            
            mkdir($thumb_dir, 0777);
            chmod($thumb_dir, 0777);
        }

        if (file_exists($main_dir . '/' . $name)) {
            chmod($main_dir . '/' . $name, 0777);
            Image::make($main_dir. '/' . $name)->resize(110, 110)->save($thumb_dir.'/'.$name);
            chmod($thumb_dir . '/' . $name, 0777);
        }
        return $name;
    }


    public function getVerificationCode($length = 12)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    /* function to send a verification email */

    public function sendVerificationMail($register_data)
    {
        $email = trim($register_data["email"]);
        $admin_email = Config::get('variable.ADMIN_EMAIL');
        $frontend_url = Config::get('variable.FRONTEND_URL');
        $user = User::where('email', $email)->first();
        Mail::send('user.register', ['data' => array("verification_token" => $register_data["verification_token"], "email" => $email,
            "frontend_url" => $frontend_url, "name" => $user->name)], function ($message) use ($email, $admin_email) {
            $message->from($admin_email, 'INAR');
            $message->to(trim($email), 'INAR')->subject('INAR : Verify Account');
        });
    }

}