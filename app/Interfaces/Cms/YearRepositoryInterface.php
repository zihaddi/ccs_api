<?php

namespace App\Interfaces\Cms;

interface YearRepositoryInterface
{
    public function index($obj, $request);
    public function show($obj, $id);
}
