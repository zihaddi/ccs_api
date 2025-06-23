<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Event\StoreEventRequest;
use App\Http\Requests\Admin\Event\UpdateEventRequest;
use App\Interfaces\Admin\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $client;

    public function __construct(EventRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Event $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Event $obj, StoreEventRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Event $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Event $obj, UpdateEventRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Event $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Event $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
