<?php

namespace App\Interfaces\Admin;

interface MediaContentRepositoryInterface
{
    public function index($obj, $request);
    public function store($obj, $request);
    public function show($obj, $id);
    public function showBySlug($obj, $slug);
    public function update($obj, $request, $id);
    public function destroy($obj, $id);
    public function restore($obj, $id);
    public function toggleFeatured($obj, $id);
    public function updateStatus($obj, $id, $status);
    public function getFeaturedContent($obj, $request);
    public function getByContentType($obj, $request, $contentType);
    public function getByChannel($obj, $request, $channelId);
    public function getPopularContent($obj, $request);
    public function getContentByNewsCategory($obj, $request, $newsCategory);
}
