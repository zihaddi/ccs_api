<?php

namespace App\Interfaces\Cms;

interface TeamMemberRepositoryInterface
{
    public function index($obj, $request);
    public function show($obj, $id);
}
