<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\PlanRepositoryInterface;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected $client;

    public function __construct(PlanRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Plan $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Plan $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Plan $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
