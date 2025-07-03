<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\TvProgramRepositoryInterface;
use App\Models\TvProgram;
use Illuminate\Http\Request;

class TvProgramController extends Controller
{
    protected $client;

    public function __construct(TvProgramRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(TvProgram $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(TvProgram $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(TvProgram $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }

    public function getByChannel(TvProgram $obj, Request $request, $channelId)
    {
        return $this->client->getByChannel($obj, $request->all(), $channelId);
    }

    public function getToday(TvProgram $obj, Request $request)
    {
        return $this->client->getToday($obj, $request->all());
    }

    public function getByType(TvProgram $obj, Request $request, $type)
    {
        return $this->client->getByType($obj, $request->all(), $type);
    }
}
