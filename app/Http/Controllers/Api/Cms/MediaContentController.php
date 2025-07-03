<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\MediaContentRepositoryInterface;
use App\Models\MediaContent;
use Illuminate\Http\Request;

class MediaContentController extends Controller
{
    protected $client;

    public function __construct(MediaContentRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(MediaContent $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(MediaContent $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(MediaContent $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }

    public function getFeatured(MediaContent $obj, Request $request)
    {
        return $this->client->getFeatured($obj, $request->all());
    }

    public function getByType(MediaContent $obj, Request $request, $contentType)
    {
        return $this->client->getByType($obj, $request->all(), $contentType);
    }

    public function getByChannel(MediaContent $obj, Request $request, $channelId)
    {
        return $this->client->getByChannel($obj, $request->all(), $channelId);
    }

    public function getPopular(MediaContent $obj, Request $request)
    {
        return $this->client->getPopular($obj, $request->all());
    }

    public function getRecent(MediaContent $obj, Request $request)
    {
        return $this->client->getRecent($obj, $request->all());
    }

    public function search(MediaContent $obj, Request $request, $searchTerm)
    {
        return $this->client->search($obj, $request->all(), $searchTerm);
    }

    public function getByNewsCategory(MediaContent $obj, Request $request, $newsCategory)
    {
        return $this->client->getByNewsCategory($obj, $request->all(), $newsCategory);
    }
}
