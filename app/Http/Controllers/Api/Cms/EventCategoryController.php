<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\EventCategoryRepositoryInterface;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    protected $client;

    public function __construct(EventCategoryRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(EventCategory $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(EventCategory $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(EventCategory $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
