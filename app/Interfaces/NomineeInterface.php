<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

use App\Http\Requests\DeclareUserRequest;
use App\Http\Requests\ForgotPinRequest;

interface NomineeInterface {
    public function getNominee(Request $request); 

    public function declareUser(DeclareUserRequest $request); 

    public function forgotPin(ForgotPinRequest $request);
}

