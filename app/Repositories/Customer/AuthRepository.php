<?php

namespace App\Repositories\Customer;

use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Enums\TokenAbility;
use App\Http\Resources\Resource;
use App\Http\Traits\Access;
use App\Http\Traits\Email;
use App\Http\Traits\FileSetup;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\SMS;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\Customer\AuthRepositoryInterface;
use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Models\UserAccountDetail;
use App\Models\UserInfo;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class AuthRepository implements AuthRepositoryInterface
{
    use FileSetup;
    use Access;
    use HttpResponses;
    use Email;
    use SMS;
    use Helper;

    protected $mobile_pattern = "/^[\+]?[0-9]{1,3}?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{3,9}$/";
    protected $auth_guard_name = '';
    protected $domain_title = '';
    protected $domain_url = '';
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->auth_guard_name = config('services.customer_auth_guard.name');
        $this->domain_title = config('services.domain_title');
        $this->domain_url = config('services.domain_url.name');
    }


    public function login($obj, $request)
    {
        $loginData = [];
        if (filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)) {
            $loginData = ['email' => $request['login_id'], 'password' => $request['password'], 'user_type' => 2];
        } elseif (preg_match($this->mobile_pattern, $request['login_id'])) {
            if ($request['ccode']) {
                $loginData = ['mobile' => str_replace($request['ccode'], '', (int)$request['login_id']), 'ccode' => $request['ccode'], 'password' => $request['password']];
            } else {
                $loginData = ['mobile' => (int)$request['login_id'], 'password' => $request['password']];
            }
        } else {
            return $this->error(null, 'Invalid email or mobile number', Response::HTTP_ACCEPTED, false);
        }
        try {
            if (Auth::guard($this->auth_guard_name)->attempt($loginData)) {
                $getUser = $obj::where('id', Auth::guard($this->auth_guard_name)->id())->with(['UserInfo'])->first();
            }
            if (isset($getUser)) {

                if ($getUser->user_type == $request['user_type']) {
                    if ($request['user_type'] == 1) {
                        $accessToken =  $getUser->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
                        $refreshToken =  $getUser->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
                    } else {
                        $accessToken =  $getUser->createToken('access_cust_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
                        $refreshToken =  $getUser->createToken('refresh_cust_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
                    }


                    $getUser['token'] = $accessToken->plainTextToken;
                    $getUser['expire_time'] = config('sanctum.ac_expiration');
                    $getUser['refresh_token'] = $refreshToken->plainTextToken;

                    return $this->success($getUser, AuthConstants::LOGIN, Response::HTTP_OK, true);
                } else {
                    return $this->error(null, AuthConstants::PERMISSION, Response::HTTP_ACCEPTED, false);
                }
            } else {
                return $this->error(null, AuthConstants::VALIDATION, Response::HTTP_ACCEPTED, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function ssoFirebaseLogin($request)
    {
        try {
            $request->validate([
                'idToken' => 'required|string',
            ]);

            $idToken = $request->idToken;
            $auth = app('firebase');
            $verifiedIdToken = $auth->verifyIdToken($idToken);

            $email = $verifiedIdToken->claims()->get('email');
            $uid = $verifiedIdToken->claims()->get('sub');
            $name = $verifiedIdToken->claims()->get('name', '');
            $picture = $verifiedIdToken->claims()->get('picture', null);
            $mobile = $verifiedIdToken->claims()->get('phoneNumber', '01700000000');

            $user = User::where('email', $email)->first();

            if (!$user) {
                DB::beginTransaction();
                try {
                    $userUuid = Str::uuid()->toString();
                    $user = new User();
                    $user->email = $email;
                    $user->password = bcrypt($uid);
                    // $user->photo = $picture;
                    $user->mobile = $mobile;
                    $user->uid = $userUuid;
                    $user->user_type = 2;
                    $user->is_verify = 1;
                    $user->status = 1;
                    $user->save();

                    if (!$user || !$user->id) {
                        throw new \Exception('Failed to create user account');
                    }

                    $nameParts = explode(' ', $name);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = count($nameParts) > 1 ? end($nameParts) : '';

                    DB::table('user_infos')->insert([
                        'user_id' => $user->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Create account details
                    $newObj = new UserAccountDetail();
                    $result = $this->storeAccountDetails($newObj, $user->id);

                    $subscriptionObj = new UserSubscription();
                    $this->storeUserSubscription($subscriptionObj, $user->id);


                    if (!$result) {
                        throw new \Exception('Failed to create user account details');
                    }

                    DB::commit();
                    $user = User::with('UserInfo')->where('id', $user->id)->first();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $this->error(null, 'Failed to create user account: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            }

            if (!$user) {
                return $this->error(null, 'User account creation failed', Response::HTTP_INTERNAL_SERVER_ERROR, false);
            }

            $accessToken = $user->createToken('access_cust_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $refreshToken = $user->createToken('refresh_cust_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            $userData = $user->load('UserInfo');
            $userData['token'] = $accessToken->plainTextToken;
            $userData['expire_time'] = config('sanctum.ac_expiration');
            $userData['refresh_token'] = $refreshToken->plainTextToken;

            return $this->success($userData, AuthConstants::LOGIN, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function refreshToken($request)
    {
        try {
            $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $success['expire_time'] = config('sanctum.ac_expiration');
            $success['token'] = $accessToken->plainTextToken;
            return $this->success($success, AuthConstants::TOCKENREGENERATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function getUser($obj, $request)
    {
        try {
            $getUser = $obj::where('id', Auth::user()->id)->with(['UserInfo'])->first();
            $getUser["token"] = $request->bearerToken();
            $getUser["token_type"] = "Bearer";
            return $this->success($getUser, Constants::GETALL, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function forgotPassword($obj, $request)
    {
        if (filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)) {
            $loginData = ['email' => $request['login_id']];
        } elseif (preg_match($this->mobile_pattern, $request['login_id'])) {
            if ($request['ccode']) {
                $loginData = ['mobile' => str_replace($request['ccode'], '', (int)$request['login_id']), 'ccode' => $request['ccode']];
            } else {
                $loginData = ['mobile' => (int)$request['login_id']];
            }
        } else {
            return $this->error(null, 'Invalid email or mobile number', Response::HTTP_BAD_REQUEST, false);
        }
        try {
            $obj = User::where($loginData)->first();
            if ($obj->uid) {
                /**
                 * Update new auth code to citizen
                 */
                try {
                    $getNewOtp = mt_rand(100000, 999999);
                    $obj->auth_code = Crypt::encryptString($getNewOtp);
                    $obj->otp_for = 'password';
                    $obj->update();
                    $auth_code = $getNewOtp;
                    $obj->auth_code = Crypt::encryptString($auth_code);

                    $applicant_data = array('domain_title' => $this->domain_title, 'otp' => $auth_code, 'url' => $this->domain_url, 'name' =>  $obj->UserInfo->first_name . ' ' . $obj->UserInfo->last_name);
                    if ($obj->mobile) {
                        $template = SmsTemplate::where('slug', 'forgot-password')->first();
                        $sms_data['number'] = $obj->ccode . (int)$obj->mobile;
                        $sms_data['msg'] = $this->bind_to_template($applicant_data, $template->sms_body);
                        try {
                            $this->sendSMS($sms_data['number'], $sms_data['msg']);
                        } catch (\Exception $e) {
                            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                        }
                    }
                    if ($obj->email) {
                        try {
                            $template = EmailTemplate::where('slug', 'forgot-password')->first();
                            $data['subject'] = $this->bind_to_template($applicant_data, $template->email_subject);
                            $data['html'] = $this->bind_to_template($applicant_data, $template->email_body);
                            $data['email'] = $obj->email;
                            $this->sendEmail($data);
                        } catch (\Exception $e) {
                            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                        }
                    }
                    $data = array('data' => $obj->only('auth_code', 'uid'));
                    return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
                } catch (\Exception $e) {
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            } else {
                return $this->error(null, AuthConstants::UNAUTHORIZED, Response::HTTP_BAD_REQUEST, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function reqLogout($request)
    {
        $auth_id = Auth::id();
        if ($auth_id) {
            try {
                $request->user()->tokens()->delete();
                return $this->success(null, AuthConstants::LOGOUT, Response::HTTP_OK, true);
            } catch (\Exception $e) {
                return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
            }
        } else {
            return $this->error(null, AuthConstants::UNAUTHORIZED, Response::HTTP_UNAUTHORIZED, true);
        }
    }

    public function reqOtpVerify($obj, $request)
    {
        try {
            $obj = $obj->where('uid', $request['uid'])->first();
            $getAuthCode = Crypt::decryptString($obj->auth_code);
            if ($getAuthCode === $request['req_otp']) {
                try {
                    if ($obj->otp_for == 'signUp') {
                        $obj->auth_code = null;
                        $obj->otp_for = null;
                        $obj->is_verify = 1;
                        $obj->status = 1;
                    } else {
                        $obj->auth_code = null;
                        $obj->otp_for = null;
                    }
                    $obj->mobile_verified_at = date('Y-m-d H:i:s');
                    $obj->update();
                    $hasdetails = UserAccountDetail::where('user_id', $obj->id)->first();
                    if ($hasdetails == null) {
                        $newObj = new UserAccountDetail();
                        $this->storeAccountDetails($newObj, $obj->id);

                        $subscriptionObj = new UserSubscription();
                        $this->storeUserSubscription($subscriptionObj, $obj->id);
                    }

                    return $this->success(new Resource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
                } catch (\Exception $e) {
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_OK, false);
            }
        } catch (DecryptException $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function reqOtpResend($obj, $request)
    {
        try {
            $getNewOtp = mt_rand(100000, 999999);
            $obj = $obj->where('uid', $request['uid'])->first();
            if ($obj == null) {
                return $this->error(null, Constants::NODATA, Response::HTTP_OK, false);
            }
            $obj->auth_code = Crypt::encryptString($getNewOtp);
            $obj->update();


            $applicant_data = array('domain_title' => $this->domain_title, 'otp' => $getNewOtp, 'url' => $this->domain_url, 'name' =>  $obj->UserInfo->first_name . ' ' . $obj->UserInfo->last_name);

            if ($obj->mobile) {
                $template = SmsTemplate::where('slug', 'forgot-password')->first();
                $sms_data['number'] = $obj->ccode . (int)$obj->mobile;
                $sms_data['msg'] = $this->bind_to_template($applicant_data, $template->sms_body);
                try {
                    $this->sendSMS($sms_data['number'], $sms_data['msg']);
                } catch (\Exception $e) {
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            }
            if ($obj->email) {
                try {
                    $template = EmailTemplate::where('slug', 'forgot-password')->first();
                    $data['subject'] = $this->bind_to_template($applicant_data, $template->email_subject);
                    $data['html'] = $this->bind_to_template($applicant_data, $template->email_body);
                    $data['email'] = $obj->email;
                    $this->sendEmail($data);
                } catch (\Exception $e) {
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            }
            $data = array('data' => $obj->only('auth_code', 'uid'));
            return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function setNewPassword($obj, $request)
    {
        try {
            $obj = $obj->where('uid', $request['uid'])->first();
            if ($obj) {
                $obj->password = bcrypt($request['password']);
                $obj->updated_at = date('Y-m-d H:i:s');
                $obj->update();
                return $this->success(new Resource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_OK, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }



    /**
     * Request for new signup
     * @param Object $request
     * @return \Illuminate\Http\Response
     */
    public function reqSignup($obj, $request)
    {
        $request['mobile'] = (int)$request['mobile'];
        $email = false;
        $mobile = false;
        if (preg_match($this->mobile_pattern, $request['mobile'])) {
            $mobile = true;
            $getData = $obj::select('*')
                ->where('mobile', 'LIKE', '%' . (int)$request['mobile'])
                ->whereNotNull('mobile')
                ->orderBy('id', 'DESC')
                ->first();
        }
        if (filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
            $email = true;
            $getData = $obj::select('*')
                ->where('email', trim($request['email']))
                ->whereNotNull('email')
                ->first();
        }

        if (!$email && !$mobile) {
            return $this->error(null, 'Invalid email or mobile number', Response::HTTP_NOT_FOUND, false);
        }
        if (preg_match($this->mobile_pattern, $request['mobile'])) {
            $mobile = true;
        }
        if (filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
            $email = true;
        }
        $getData = $obj::select('*')
            ->where('email', trim($request['email']))
            ->orWhere('mobile', 'LIKE', '%' . (int)$request['mobile'])
            ->first();
        if (!empty($getData) && $getData->is_verify > 0) {
            $data = array('registered' => true, 'req_id_type' => $email ? 'email' : 'mobile', 'data' => $getData);
            return $this->success($data, AuthConstants::VERIFYED, Response::HTTP_OK, true);
        } elseif (!empty($getData) && !$getData->is_verify) {
            $auth_code = $getData->auth_code;
            $getData->auth_code = Crypt::encryptString($auth_code);
            $data = array('data' => $getData->only('uid', 'auth_code'));
            return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
        }

        /**
         * @param string $mobile
         * @param string $email
         * @param string $auth_code
         */
        $getNewOtp = mt_rand(100000, 999999);


        DB::beginTransaction();
        try {
            $userId = DB::table('users')->insertGetId([
                'mobile' => (int)$request['mobile'],
                'ccode' => $request['ccode'],
                'email' => $request['email'] ?: null,
                'auth_code' => Crypt::encryptString($getNewOtp),
                'otp_for' => 'signUp',
                'status' => 0,
                'user_type' => 2
            ]);
            if ($userId) {
                $getLastId = $userId;
                $applicant_data = array('domain_title' => $this->domain_title, 'otp' => $getNewOtp, 'url' => $this->domain_url, 'name' => $request['first_name'] . ' ' . $request['last_name']);
                if ($mobile) {
                    $template = SmsTemplate::where('slug', 'sign-up')->first();
                    $sms_data['number'] = $request['ccode'] . (int)$request['mobile'];
                    $sms_data['msg'] = $this->bind_to_template($applicant_data, $template->sms_body);

                    try {
                        $this->sendSMS($sms_data['number'], $sms_data['msg']);
                    } catch (\Exception $e) {
                        $this->rollback($e->getMessage());
                        return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                    }
                }
                if ($email) {
                    try {
                        $template = EmailTemplate::where('slug', 'sign-up')->first();
                        $data['subject'] = $this->bind_to_template($applicant_data, $template->email_subject);
                        $data['html'] = $this->bind_to_template($applicant_data, $template->email_body);
                        $data['email'] = $request['email'];
                        $this->sendEmail($data);
                    } catch (\Exception $e) {
                        $this->rollback($e->getMessage());
                        return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                    }
                }
                try {
                    DB::table('user_infos')->insert([
                        'user_id' => $userId,
                        'first_name' => $request['first_name'],
                        'last_name' => $request['last_name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    $this->rollback($e->getMessage());
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
                DB::commit();

                $getData = $obj->where('id', $getLastId)->select('auth_code', 'uid')->first();

                $getData->auth_code = $getData->auth_code;
                $data = array('data' => $getData);
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            }
        } catch (\Exception $e) {
            $this->rollback($e->getMessage());
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    /**
     * Profie photo upload
     *
     * @param  mixed $obj
     * @param  mixed $request
     * @param  mixed $uid
     * @return \Illuminate\Http\Response
     */
    public function uploadPhoto($obj, $request)
    {
        // return $request->all();
        $auth_id = Auth::id();
        /**
         * IMAGE CONVERT
         */
        $target_path = 'customer-photos/' . $auth_id;
        if ($request['photo']) {
            $request['photo'] = $target_path . '/' . $this->base64ToImage($request['photo'], $target_path);
        }
        $obj = $obj->where('id', $auth_id)->first();

        try {
            $userObj = User::where('id', $obj->id)->first();
            if ($request['photo']) {
                // Remove previous image
                if ($userObj->photo) {
                    Storage::disk(config('services.storage_disk'))->delete($userObj->photo);
                }
            }
            // foreach ($request as $key => $val) {
            //     $userObj[$key] = $val;
            // }
            $userObj->photo = $request['photo'];
            $userObj->save();
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
        return $this->success(new Resource($obj->only('mobile', 'ccode', 'email', 'photo', 'username', 'userInfo')), Constants::UPDATE, Response::HTTP_CREATED, true);
    }

    public function storeAccountDetails($obj, $id)
    {
        try {

            $start_date = Carbon::now();
            $expiry_date = $start_date->copy()->addDays(7);
            $renewal_date = $expiry_date->copy()->addYear();
            $reqData = [
                'user_id' => $id,
                'plan_id' => 1, // Assuming '1' is the ID for the basic plan
                'number_of_websites' => 1, // Assuming '1' based on the basic plan
                'start_date' => $start_date,
                'renewal_date' => $renewal_date,
                'expiry_date' => $expiry_date,
                'api_key' => Str::random(40),
                'is_active' => true,
                'is_trial' => true,
                'is_expired' => false,
                'status' => 'free',
            ];
            $userAccountDetail = $obj::create($reqData);
            if (!$userAccountDetail) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function storeUserSubscription($obj, $id)
    {
        try {

            $reqData = [
                'user_id' => $id,
                'plan_id' => 1, // Assuming '1' is the ID for the basic plan
                'subscription_type' => 'trial', // Assuming 'trial' is the type for the basic plan
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYear(),
                'status' => 'active',
                'next_billing_date' => Carbon::now()->addYear(),
                'amount' => 0, // Assuming '0' for the trial period
                'currency' => 'USD', // Assuming USD as the currency
                'auto_renew' => true,
            ];
            $userAccountDetail = $obj::create($reqData);
            if (!$userAccountDetail) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateUserSubscription($obj, $request, $id)
    {
        try {
            $userAccountDetail = $obj::find($id);
            if ($userAccountDetail) {
                $userAccountDetail->update([
                    'plan_id' => $request['plan_id'],
                    'subscription_type' => $request['subscription_type'],
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addYear(),
                    'status' => 'active',
                    'next_billing_date' => Carbon::now()->addYear(),
                    'amount' => $request['amount'],
                    'currency' => $request['currency'],
                    'auto_renew' => $request['auto_renew'],
                ]);
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateUserAccountDetails($obj, $request, $id)
    {
        try {
            $userAccountDetail = $obj::find($id);
            if ($userAccountDetail) {
                $userAccountDetail->update([
                    'plan_id' => $request['plan_id'],
                    'number_of_websites' => $request['number_of_websites'],
                    'start_date' => Carbon::now(),
                    'renewal_date' => Carbon::now()->addYear(),
                    'expiry_date' => Carbon::now()->addYear(),
                    'api_key' => Str::random(40),
                    'is_active' => $request['is_active'],
                    'is_trial' => $request['is_trial'],
                    'is_expired' => $request['is_expired'],
                    'status' => $request['status']
                ]);
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
