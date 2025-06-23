<?php

namespace App\Interfaces\Customer;

interface UserRepositoryInterface
{
    public function updateAccountInformation($obj, $request, $id);

    public function updateAccountDetails($obj, $request, $id);
}
