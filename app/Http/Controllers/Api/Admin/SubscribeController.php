<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subscribe\SubscribeStoreRequest;
use App\Http\Requests\Admin\Subscribe\SubscribeUpdateRequest;
use App\Interfaces\Admin\SubscribeRepositoryInterface;
use App\Models\Subscribe;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    protected $client;

    public function __construct(SubscribeRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Subscribe $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Subscribe $obj, SubscribeStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Subscribe $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Subscribe $obj, SubscribeUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Subscribe $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Subscribe $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
