<?php

namespace App\Interfaces;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\UserChangePasswordRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserProfileImageRequest;
use App\Http\Requests\UserProfileRequest;
use App\Http\Requests\UserResendVerifyRequest;
use App\Http\Requests\UserResetPasswordRequest;
use App\Http\Requests\UserSignupRequest;
use App\Http\Requests\UserVerifyRequest;
use Illuminate\Http\Request;

interface UserInterface {
    public function validateForgotPasswordExpiry(Request $request);

    public function signUp(UserSignupRequest $request);

    public function login(UserLoginRequest $request);

    public function logout(Request $request);

    public function resendVerification(UserResendVerifyRequest $request);

    public function verify(UserVerifyRequest $request);

    public function forgotPassword(ForgotPasswordRequest $request);

    public function resetPassword(UserResetPasswordRequest $request);

    public function changePassword(UserChangePasswordRequest $request);

    public function getPersonalProfile(Request $request);

    public function updatePersonalProfile(UserProfileRequest $request);

    public function uploadProfileImage(UserProfileImageRequest $request);

    public function getPackages(Request $request);    

    public function getUser(Request $request); 

    public function changeEmail(Request $request); 

    public function verifyChangeEmail(Request $request); 
}

