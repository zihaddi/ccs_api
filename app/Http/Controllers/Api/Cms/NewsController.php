<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\NewsRepositoryInterface;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    protected $client;

    public function __construct(NewsRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(News $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(News $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(News $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
