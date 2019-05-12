<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Stripe\Stripe;
use Response;
class WebhookController extends CashierController
{
    public function paymentSucceeded($payload)
    {
        $collection = collect($payload);
        $data = $collection->toArray();
        if (isset($data) && !empty($data['type'])) {
            $get_customer_id = $data["data"]["object"]["customer"]; //'cus_EGxeAOKyzn2BLe';
            $user_data = User::where('stripe_id', $get_customer_id)->first();
            if (isset($user_data) && !empty($user_data->id)) {
                $array['plan_id'] = $data["data"]["object"]["lines"]["data"][0]["plan"]["id"];
                $array['amount'] = $data["data"]["object"]["amount_paid"];
                $array['current_period_start'] = $data["data"]["object"]["lines"]["data"][0]["period"]["start"];
                $array['current_period_end'] = $data["data"]["object"]["lines"]["data"][0]["period"]["end"];
                $array['updated_at'] = time();
                $array['charge_id'] = $data["data"]["object"]["lines"]["data"][0]["subscription"];
                UserPackage::where("user_id", $user_data->id)->update($array);
            }
        }
        echo 'Fired';
    }

    public function paymentFailed($payload)
    {
        $collection = collect($payload);
        $data = $collection->toArray();
        if (isset($data) && !empty($data['type'])) {
            $get_customer_id = $data["data"]["object"]["customer"]; //'cus_EGxeAOKyzn2BLe';
            $user_data = User::where('stripe_id', $get_customer_id)->first();
            if (isset($user_data) && !empty($user_data->id)) {
                //$array['plan_id'] = $data["data"]["object"]["lines"]["data"][0]["plan"]["id"];
                //$array['amount'] = $data["data"]["object"]["amount_paid"];
                //$array['current_period_start'] = $data["data"]["object"]["lines"]["data"][0]["period"]["start"];
                //$array['current_period_end'] = $data["data"]["object"]["lines"]["data"][0]["period"]["end"];
                $array['updated_at'] = time();
                $array['charge_id'] = $data["data"]["object"]["lines"]["data"][0]["subscription"];
                $array['canceled_at'] = time();
                UserPackage::where("user_id", $user_data->id)->update($array);
            }
        }
        echo 'Fired';
    }

    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey('sk_test_cnh5NIVfv4P24BjFcryX09w7');
        try {
            $payload = @file_get_contents('php://input');
            $event = \Stripe\Webhook::constructEvent(
                $payload, $_SERVER['HTTP_STRIPE_SIGNATURE'], config('variable.WEBHOOK_TOKEN')
            );
            // Handle The Event
            switch ($event->type) {
                case "invoice.payment_succeeded":
                    return $this->paymentSucceeded($event);
                    break;
                case "invoice.payment_failed":
                    return $this->paymentFailed($event);
                    break;
            }
        } catch (\UnexpectedValueException $e) {
            http_response_code(400); // PHP 5.4 or greater
            $error["status"] = 400;
            $error["message"] = $e->getMessage();
            return $error;
            exit();
        } catch (\Stripe\Error\SignatureVerification $e) {
            http_response_code(400); // PHP 5.4 or greater
            $error["status"] = 400;
            $error["message"] = $e->getMessage();
            return $error;
            // Invalid signature
            exit();
        }

    }

}
