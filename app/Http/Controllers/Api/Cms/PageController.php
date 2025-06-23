<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\PageRepositoryInterface;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected $client;

    public function __construct(PageRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Page $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Page $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Page $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
