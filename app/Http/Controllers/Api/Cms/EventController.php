<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $client;

    public function __construct(EventRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Event $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Event $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Event $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
    public function upcoming(Event $obj, Request $request)
    {
        return $this->client->getUpcomingEvents($obj, $request->all());
    }

    public function completed(Event $obj, Request $request)
    {
        return $this->client->getCompletedEvents($obj, $request->all());
    }
}
