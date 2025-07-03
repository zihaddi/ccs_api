<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\TvChannelRepositoryInterface;
use App\Models\TvChannel;
use Illuminate\Http\Request;

class TvChannelController extends Controller
{
    protected $client;

    public function __construct(TvChannelRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(TvChannel $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(TvChannel $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(TvChannel $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
