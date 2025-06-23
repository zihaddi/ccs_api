<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    protected $client;

    public function __construct(TagRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Tag $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Tag $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Tag $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
