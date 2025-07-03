<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaContent\MediaContentStoreRequest;
use App\Http\Requests\Admin\MediaContent\MediaContentUpdateRequest;
use App\Interfaces\Admin\MediaContentRepositoryInterface;
use App\Models\MediaContent;
use Illuminate\Http\Request;

class MediaContentController extends Controller
{
    protected $client;

    public function __construct(MediaContentRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update', 'toggleFeatured', 'updateStatus']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(MediaContent $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(MediaContent $obj, MediaContentStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(MediaContent $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(MediaContent $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }

    public function update(MediaContent $obj, MediaContentUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(MediaContent $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(MediaContent $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }

    public function toggleFeatured(MediaContent $obj, $id)
    {
        return $this->client->toggleFeatured($obj, $id);
    }

    public function updateStatus(MediaContent $obj, Request $request, $id)
    {
        return $this->client->updateStatus($obj, $id, $request->status);
    }

    public function getFeatured(MediaContent $obj, Request $request)
    {
        return $this->client->getFeaturedContent($obj, $request->all());
    }

    public function getByType(MediaContent $obj, Request $request, $contentType)
    {
        return $this->client->getByContentType($obj, $request->all(), $contentType);
    }

    public function getByChannel(MediaContent $obj, Request $request, $channelId)
    {
        return $this->client->getByChannel($obj, $request->all(), $channelId);
    }

    public function getPopular(MediaContent $obj, Request $request)
    {
        return $this->client->getPopularContent($obj, $request->all());
    }

    public function getByNewsCategory(MediaContent $obj, Request $request, $newsCategory)
    {
        return $this->client->getContentByNewsCategory($obj, $request->all(), $newsCategory);
    }
}
