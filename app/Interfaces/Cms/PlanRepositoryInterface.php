<?php

namespace App\Interfaces\Cms;

interface PlanRepositoryInterface
{
    public function index($obj, $request);
    public function show($obj, $request);
    public function showBySlug($obj, $slug);
}
