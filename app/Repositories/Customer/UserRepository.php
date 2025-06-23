<?php

namespace App\Repositories\Customer;

use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Traits\Access;
use App\Http\Traits\FileSetup;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Customer\UserRepositoryInterface;
use Illuminate\Support\Str;
class UserRepository implements UserRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;
    protected $image_target_path = 'images/compliance-image';
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Update account information.
     * 
     */
    public function updateAccountInformation($obj, $request, $id)
    {
        try {
            $user = $obj->find($id);
            if (!$user) {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            } else {
                $user->update($request);
                return $this->success($user, Constants::GETALL, Response::HTTP_OK, true);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }    

    /**
     * Update user account details.
     * 
     * @param mixed $obj 
     * @param mixed $request 
     * @param int $id 
     * 
     * @return JsonResponse
     */
    public function updateAccountDetails($obj, $request, $id)
    {
        try {
            $userAccountDetail = $obj::find($id);
            if ($userAccountDetail) {
                $userAccountDetail->update($request);
                return $this->success($userAccountDetail, Constants::UPDATE, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::FAILUPDATE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
