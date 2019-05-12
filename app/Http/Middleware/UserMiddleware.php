<?php

namespace App\Http\Middleware;

use App\User;
use Auth;
use Closure;
use Exception;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request['data'] = User::where('id', Auth::user()->id)->where(["status" => 1])
            ->select('id', 'name', 'email', 'role_id', 'status', 'image', 'stripe_id', 'primary_declaration','guarantee_declaration','declaration_date','temp_primary_declaration','temp_guarantee_declaration','temp_declaration_date')
            ->withCount('notification')
            ->with(['role' => function ($q) {
                $q->select('id', 'name', 'slug');
            },'packages' => function ($q) {
                    $q->select('id','charge_id',  'package_id', 'user_id', 'type', 'status', 'current_period_start', 'current_period_end','canceled_at','cancel_at_period_end')
                    ->whereIn('type', [1,2])
                    ->whereIn('status',  [0,1]);
                },
                'packages.details' => function ($q) {
                    $q->select('id','slug', 'name', 'audio_limit', 'video_limit', 'document_limit', 'image_limit', 'members_count_limit', 'amount', 'subscription_days', 'status')
                        ->where(["status" => 1]);
                },
            ])->first();
            if($request['data']["packages"]["current_period_end"] != 0){
       // if($request['data']["packages"]["cancel_at_period_end"] == 1 || $request['data']["packages"]["cancel_at_period_end"] == 0){
            //get active entry from the databse
            $res = [];
            $res = User::where('id', Auth::user()->id)->where(["status" => 1])
            ->select('id', 'name', 'email', 'role_id', 'status', 'image', 'stripe_id', 'primary_declaration','guarantee_declaration','declaration_date','temp_primary_declaration','temp_guarantee_declaration','temp_declaration_date')
            ->with(['packages' => function ($q) {
                    $q->select('id', 'charge_id', 'package_id', 'user_id', 'type', 'status', 'current_period_start', 'current_period_end','canceled_at','cancel_at_period_end')
                    ->where('type', 2)
                    ->where('current_period_end',  '>' , time())->orderBy('id','desc');
                },
                'packages.details' => function ($q) {
                    $q->select('id','slug', 'name', 'audio_limit', 'video_limit', 'document_limit', 'image_limit', 'members_count_limit', 'amount', 'subscription_days', 'status')
                        ->where(["status" => 1]);
                },
            ])->first();
            unset($request['data']["packages"]);
            $request['data']["packages"] = (!empty($res["packages"]) && isset($res)) ? $res["packages"] : '';
        } 
       
             
        return $next($request);
    }
}
