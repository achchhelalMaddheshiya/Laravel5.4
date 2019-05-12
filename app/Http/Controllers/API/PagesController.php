<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUs;
use Config;
use Illuminate\Http\Request;
use App\Interfaces\PagesInterface;
use Mail;
use Response;

class PagesController extends Controller implements PagesInterface
{
    public function contactUs(ContactUs $request)
    {
        $data = [];
        $requested_data = $request->all();

        $name = $requested_data["name"];
        $email = $requested_data["email"];
        $subject = $requested_data["subject"];
        $contact_message = $requested_data["message"];
       

        $admin_email = Config::get('variable.ADMIN_EMAIL');
        $frontend_url = Config::get('variable.FRONTEND_URL');

        Mail::send('emails.pages.contact_us', ['data' => array("MailToUser" => '', "name" => $name, "email" => $email,
            "frontend_url" => $frontend_url, "subject" => $subject, "message" => $contact_message)], function ($message) use ($email, $admin_email, $subject, $contact_message) {
            $message->from($email, config('variable.SITE_NAME'));
            $message->to($admin_email, config('variable.SITE_NAME'))->subject(config('variable.SITE_NAME').' : Contact us');
        });

        Mail::send('emails.pages.contact_us', ['data' => array("MailToUser" => 'Yes', "name" => $name, "email" => $email,
            "frontend_url" => $frontend_url, "subject" => $subject, "message" => $contact_message)], function ($message) use ($email, $admin_email, $subject, $contact_message) {
            $message->from($admin_email, config('variable.SITE_NAME'));
            $message->to(trim($email), config('variable.SITE_NAME'))->subject(config('variable.SITE_NAME').' : Contact us');
        });
        if(count(Mail::failures()) > 0){
            $data['status'] = 400;
            $data['message'] = 'There was an error while sending mail';
        }else{
            $data['message'] = 'Thank you, your request has been submitted, we will reach you soon';
            $data['status'] = 200;
        }
        return Response::json($data);
    }
}
