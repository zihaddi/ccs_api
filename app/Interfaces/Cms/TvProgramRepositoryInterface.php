<?php

namespace App\Interfaces\Cms;

interface TvProgramRepositoryInterface
{
    public function index($obj, $request);
    public function show($obj, $id);
    public function showBySlug($obj, $slug);
    public function getByChannel($obj, $request, $channelId);
    public function getToday($obj, $request);
    public function getByType($obj, $request, $type);
}
