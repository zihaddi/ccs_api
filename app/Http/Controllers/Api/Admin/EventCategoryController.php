<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventCategory\StoreEventCategoryRequest;
use App\Http\Requests\Admin\EventCategory\UpdateEventCategoryRequest;
use App\Interfaces\Admin\EventCategoryRepositoryInterface;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    protected $client;

    public function __construct(EventCategoryRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(EventCategory $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(EventCategory $obj, StoreEventCategoryRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(EventCategory $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(EventCategory $obj, UpdateEventCategoryRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(EventCategory $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(EventCategory $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
