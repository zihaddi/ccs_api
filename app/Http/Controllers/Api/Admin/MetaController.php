<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Meta\MetaStoreRequest;
use App\Http\Requests\Admin\Meta\MetaUpdateRequest;
use App\Interfaces\Admin\MetaRepositoryInterface;
use App\Models\Meta;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    protected $client;

    public function __construct(MetaRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Meta $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Meta $obj, MetaStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Meta $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Meta $obj, MetaUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Meta $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Meta $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
