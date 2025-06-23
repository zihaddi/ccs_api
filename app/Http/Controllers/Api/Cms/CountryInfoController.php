<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\CountryInfoRepositoryInterface;
use App\Models\CountryInfo;
use Illuminate\Http\Request;

class CountryInfoController extends Controller
{

    protected $client;

    public function __construct(CountryInfoRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(CountryInfo $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(CountryInfo $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(CountryInfo $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
