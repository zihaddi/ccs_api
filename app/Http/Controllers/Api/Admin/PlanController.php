<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Plan\PlanStoreRequest;
use App\Http\Requests\Admin\Plan\PlanUpdateRequest;
use App\Interfaces\Admin\PlanRepositoryInterface;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected $client;

    public function __construct(PlanRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Plan $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Plan $obj, PlanStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Plan $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Plan $obj, PlanUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Plan $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Plan $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
