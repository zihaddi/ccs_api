<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\PortfolioRepositoryInterface;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    protected $client;

    public function __construct(PortfolioRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Portfolio $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Portfolio $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Portfolio $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
