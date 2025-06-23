<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected $client;

    public function __construct(BrandRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Brand $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Brand $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Brand $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
