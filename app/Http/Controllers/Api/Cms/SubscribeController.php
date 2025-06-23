<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\SubscribeRepositoryInterface;
use App\Models\Subscribe;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    protected $client;

    public function __construct(SubscribeRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Subscribe $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Subscribe $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Subscribe $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
