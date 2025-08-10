<?php

namespace App\Interfaces\Cms;

interface EventRepositoryInterface
{
    public function index($obj, $request);
    public function show($obj, $request);
    public function showBySlug($obj, $slug);
    public function getUpcomingEvents($obj, $request);
    public function getCompletedEvents($obj, $request);
}
