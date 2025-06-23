<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\MetaRepositoryInterface;
use App\Models\Meta;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    protected $client;

    public function __construct(MetaRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Meta $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Meta $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Meta $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
