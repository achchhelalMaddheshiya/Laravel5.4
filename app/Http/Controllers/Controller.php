<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Config;

/**
     * @SWG\Swagger(
     *     schemes={"http"},
     *     host="http://server.willodiary.com",
     *     basePath="/api",
     *     @SWG\Info(
     *         version="1.0.0",
     *         title="Willodiary",
     *         description="Swagger creates human-readable documentation for your APIs.",
     *     )
     * )
     */
class Controller extends BaseController
{
    //
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
