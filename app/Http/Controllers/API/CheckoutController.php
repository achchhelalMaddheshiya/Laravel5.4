<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnsubscribePaymentRequest;
use App\Interfaces\PaymentInterface;
use App\Package;
use App\User;
use App\UserPackage;
use Illuminate\Http\Request;
use Response;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Plan;
use Stripe\Stripe;
use Stripe\Subscription;

class CheckoutController extends Controller implements PaymentInterface
{
    private function createCustomer($requested_data)
    {
        Stripe::setApiKey('sk_test_cnh5NIVfv4P24BjFcryX09w7');
        $user = User::where('id', $requested_data["data"]["id"])->first();
        if ($user->stripe_id && $user->stripe_id != '') {
            return $user->stripe_id;
        } else {
            $customer = Customer::create(array(
                'email' => $user->email,
                'source' => $requested_data['token'],
            ));
            User::where('id', $requested_data["data"]["id"])->update(["stripe_id" => $customer->id]);
            return $customer->id;
        }
    }

    private function isValidPlan($requested_data)
    {
        $error = [];
        //Check valid plan id and get amount according to that
        $packages = Package::where('id', $requested_data["package_id"])->first();
        //Check plan id available at stripe or not
        Stripe::setApiKey('sk_test_cnh5NIVfv4P24BjFcryX09w7');
        try {
            $stripe_plans = \Stripe\Plan::retrieve($packages->slug);
            if ($stripe_plans->id && $stripe_plans->active == true) {
                $error["status"] = 200;
                $error["message"] = "Plan exists";

                $error["data"]["plan_id"] = $stripe_plans->id;
                $error["data"]["active"] = $stripe_plans->active;
                $error["data"]["amount"] = $stripe_plans->amount;
            } else {
                $error["status"] = 400;
                $error["message"] = "Plan doesnot exists";
            }
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $body = $e->getJsonBody();
            $err = $body['error'];
            $error["status"] = 400;
            $error["message"] = $err['message'];
        } catch (Exception $e) {
            $error["status"] = 400;
            $error["message"] = $e->getMessage();
        }
        return $error;
    }

    public function charge(Request $request)
    {
        Stripe::setApiKey('sk_test_cnh5NIVfv4P24BjFcryX09w7');
        $requested_data = $request->all();
        $user_data = $requested_data["data"];
        $is_valid_plan = $this->isValidPlan($requested_data);

        if ($is_valid_plan["status"] == 400) {
            return Response::json($is_valid_plan);
        }
        $user_created = $this->createCustomer($requested_data);

        try {
            $subscription_detail = [];
            $user = User::where('id', $user_data["id"])->first();
            // all plan purchased and canceled now trying to buy again
            if ($requested_data["data"]["packages"] == '') {
                //get users current plan entry
                $res = UserPackage::where('user_id', $user_data["id"])->first();

                $recurringData = \Stripe\Subscription::create([
                    "customer" => $user_created,
                    "items" => [
                        [
                            "plan" => $is_valid_plan["data"]["plan_id"],
                        ],
                    ],
                ]);
                if ($recurringData->id) {
                    UserPackage::where('id', $res->id)->update([
                        'type' => 2,
                        'status' => 1,
                        'current_period_start' => $recurringData->current_period_start,
                        'current_period_end' => $recurringData->current_period_end,
                        'package_id' => $requested_data["package_id"],
                        'canceled_at' => 0,
                        'cancel_at_period_end' => 0,
                        'updated_at' => time(),
                        'charge_id' => $recurringData->id,
                        'current_period_start' => $recurringData->current_period_start,
                        'current_period_end' => $recurringData->current_period_end,
                        'amount' => $is_valid_plan["data"]["amount"],
                    ]);
                }

                $data['data'] = $recurringData;
                $data['message'] = 'Your payment has been completed successfully';
                $data['status'] = 200;
                return Response::json($data);
            }

            // check if there is any plan that user has opted if yes then upgrade else buy
            if ($requested_data["data"]["packages"]["details"]['slug'] == 'free') {
                $recurringData = \Stripe\Subscription::create([
                    "customer" => $user_created,
                    "items" => [
                        [
                            "plan" => $is_valid_plan["data"]["plan_id"],
                        ],
                    ],
                ]);
                if ($recurringData->id) {
                    UserPackage::where('id', $requested_data["data"]["packages"]["id"])->update([
                        'type' => 2,
                        'status' => 1,
                        'current_period_start' => $recurringData->current_period_start,
                        'current_period_end' => $recurringData->current_period_end,
                        'package_id' => $requested_data["package_id"],
                        'canceled_at' => 0,
                        'cancel_at_period_end' => 0,
                        'updated_at' => time(),
                        'charge_id' => $recurringData->id,
                        'amount' => $recurringData->items->data[0]->plan->amount,
                    ]);
                }

                $data['data'] = $recurringData;
                $data['message'] = 'Your payment has been completed successfully';
                $data['status'] = 200;
                return Response::json($data);
            } else {
                $subscription = \Stripe\Subscription::retrieve($requested_data["data"]["packages"]["charge_id"]);
                $subscription->cancel_at_period_end = false;
                $subscription->billing_cycle_anchor = 'now';
                $subscription->prorate = true;
                $subscription->plan = $is_valid_plan["data"]["plan_id"];

                if ($subscription->save()) {
                    UserPackage::where('id', $requested_data["data"]["packages"]["id"])->update([
                        'status' => 1,
                        'current_period_start' => $subscription->current_period_start,
                        'current_period_end' => $subscription->current_period_end,
                        'package_id' => $requested_data["package_id"],
                        'canceled_at' => 0,
                        'cancel_at_period_end' => 0,
                        'updated_at' => time(),
                        'amount' => $is_valid_plan["data"]["amount"],
                    ]);
                    $data['data'] = $subscription;
                    $data["status"] = 200;
                    $data["message"] = 'Plan has been successfully upgraded';
                } else {
                    $data["status"] = 400;
                    $data["message"] = 'Not able to upgrade plan';
                }
                return Response::json($data);
            }

        } catch (\Stripe\Error\Card $e) {
            $body = $e->getJsonBody(); // Since it's a decline, \Stripe\Error\Card will be caught
            $err = $body['error'];
            $data["status"] = 400;
            $data["message"] = $err['message'];
            return Response::json($data);
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $body = $e->getJsonBody();
            $err = $body['error'];
            $data["status"] = 400;
            $data["message"] = $err['message'];
            return Response::json($data);
        } catch (Exception $e) {
            $data["status"] = 400;
            $data["message"] = $e->getMessage();
            return Response::json($data);
        }

    }

    public function unsubscribePayment(UnsubscribePaymentRequest $request)
    {
        Stripe::setApiKey('sk_test_cnh5NIVfv4P24BjFcryX09w7');
        $requested_data = $request->all();
        $response = UserPackage::where(["id" => $requested_data["id"]])->first()->toArray();

        if (isset($response) && !empty($response)) {
            $charge_id = $response["charge_id"];
            $subscription = \Stripe\Subscription::retrieve($charge_id);
            $subscription->cancel_at_period_end = true;
            if ($subscription->save()) {
                UserPackage::where('id', $requested_data["id"])->update(['status' => 0, 'canceled_at' => $subscription->canceled_at, 'cancel_at_period_end' => 1]);
                $data["status"] = 200;
                $data["message"] = 'Package unsubscribed successfully';
            } else {
                $data["status"] = 400;
                $data["message"] = 'Not able to unsubscribe';
            }
        } else {
            $data["status"] = 400;
            $data["message"] = 'Not a valid entry point';
        }
        return Response::json($data);
    }

}
