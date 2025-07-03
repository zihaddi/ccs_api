<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TvChannel\TvChannelStoreRequest;
use App\Http\Requests\Admin\TvChannel\TvChannelUpdateRequest;
use App\Interfaces\Admin\TvChannelRepositoryInterface;
use App\Models\TvChannel;
use Illuminate\Http\Request;

class TvChannelController extends Controller
{
    protected $client;

    public function __construct(TvChannelRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(TvChannel $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(TvChannel $obj, TvChannelStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(TvChannel $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(TvChannel $obj, TvChannelUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(TvChannel $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(TvChannel $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
