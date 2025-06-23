<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Feature\FeatureStoreRequest;
use App\Http\Requests\Admin\Feature\FeatureUpdateRequest;
use App\Interfaces\Admin\FeatureRepositoryInterface;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    protected $client;

    public function __construct(FeatureRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Feature $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Feature $obj, FeatureStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Feature $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Feature $obj, FeatureUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Feature $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Feature $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
