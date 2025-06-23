<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\FeatureRepositoryInterface;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    protected $client;

    public function __construct(FeatureRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Feature $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Feature $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Feature $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
