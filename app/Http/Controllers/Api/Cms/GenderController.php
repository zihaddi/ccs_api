<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\GenderRepositoryInterface;
use App\Models\Gender;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    protected $client;

    public function __construct(GenderRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Gender $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Gender $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Gender $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
