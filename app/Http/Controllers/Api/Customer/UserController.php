<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\User\StoreUserDetailRequest;
use App\Http\Requests\Customer\User\UpdateUserDetailRequest;
use App\Http\Requests\Customer\User\UserRequest;
use App\Interfaces\Customer\UserRepositoryInterface;
use App\Models\User;
use App\Models\UserAccountDetail;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $client;

    public function __construct(UserRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function updateAccountInformation(User $object, UserRequest $request, $id)
    {
        return $this->client->updateAccountInformation($object, $request->validated(), $id);
    }


    public function updateAccountDetails(UserAccountDetail $object, UpdateUserDetailRequest $request, $id)
    {
        return $this->client->updateAccountDetails($object, $request->validated(), $id);
    }
}
