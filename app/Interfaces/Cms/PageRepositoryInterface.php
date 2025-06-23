<?php

namespace App\Interfaces\Cms;

interface PageRepositoryInterface
{
    public function index($obj, $request);
    public function show($obj, $request);
    public function showBySlug($obj, $slug);
}
