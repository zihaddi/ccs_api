<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\TutorialRepositoryInterface;
use App\Models\Tutorial;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    protected $client;

    public function __construct(TutorialRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Tutorial $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Tutorial $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Tutorial $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
