<?php

namespace App\Interfaces\Cms;

interface TvChannelRepositoryInterface
{
    public function index($obj, $request);
    public function show($obj, $id);
    public function showBySlug($obj, $slug);
}
