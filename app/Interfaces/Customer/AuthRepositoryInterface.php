<?php

namespace App\Interfaces\Customer;

interface AuthRepositoryInterface
{
    public function login($obj, $data);
    public function ssoFirebaseLogin($request);
    public function refreshToken($request);
    public function getUser($obj, $request);
    public function forgotPassword($obj, $request);
    public function reqLogout($request);
    public function reqOtpVerify($obj, $request);
    public function reqOtpResend($obj, $request);
    public function setNewPassword($obj, $request);
    public function reqSignup($obj, $request);
    public function uploadPhoto($obj, $request);
    public function storeAccountDetails($obj, $id);
}
