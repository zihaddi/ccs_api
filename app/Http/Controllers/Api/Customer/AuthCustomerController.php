<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\AuthRequest;
use App\Http\Requests\Customer\Auth\ForgotPasswordRequest;
use App\Http\Requests\Customer\Auth\OtpResendRequest;
use App\Http\Requests\Customer\Auth\OtpVerifyRequest;
use App\Http\Requests\Customer\Auth\PhotoUploadRequest;
use App\Http\Requests\Customer\Auth\SetNewPasswordRequest;
use App\Http\Requests\Customer\Auth\SignUpRequest;
use App\Interfaces\Customer\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;

class AuthCustomerController extends Controller
{
    protected $client;

    public function __construct(AuthRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function login(User $obj, Request $request)
    {
        $request->merge(['user_type' => '2']);
        return $this->client->login($obj, $request);
    }

    public function ssoFirebaseLogin(Request $request)
    {
        return $this->client->ssoFirebaseLogin($request);
    }
    public function refreshToken(Request $request)
    {
        return $this->client->refreshToken($request);
    }
    public function logout(Request $request)
    {
        return $this->client->reqLogout($request);
    }

    public function getUser(User $obj, Request $request)
    {
        return $this->client->getUser($obj, $request);
    }
    public function forgotPassword(User $obj, ForgotPasswordRequest $request)
    {
        return $this->client->forgotPassword($obj, $request);
    }

    public function reqOtpVerify(User $obj, OtpVerifyRequest $request)
    {
        return $this->client->reqOtpVerify($obj, $request);
    }

    public function reqOtpResend(User $obj, OtpResendRequest $request)
    {
        return $this->client->reqOtpResend($obj, $request);
    }

    public function setNewPassword(User $obj, SetNewPasswordRequest $request)
    {
        return $this->client->setNewPassword($obj, $request);
    }

    public function reqSignup(User $obj, SignUpRequest $request)
    {
        return $this->client->reqSignup($obj, $request);
    }

    public function uploadPhoto(User $obj, PhotoUploadRequest $request)
    {
        return $this->client->uploadPhoto($obj, $request);
    }
}
