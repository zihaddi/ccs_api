<?php

namespace App\Interfaces\Cms;

interface MediaContentRepositoryInterface
{

    public function index($obj, $request);


    public function show($obj, $id);


    public function showBySlug($obj, $slug);


    public function getFeatured($obj, $request);


    public function getByType($obj, $request, $contentType);


    public function getByChannel($obj, $request, $channelId);


    public function getPopular($obj, $request);


    public function getRecent($obj, $request);


    public function search($obj, $request, $searchTerm);


    public function getByNewsCategory($obj, $request, $newsCategory);
}
