<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Gender\GenderStoreRequest;
use App\Http\Requests\Admin\Gender\GenderUpdateRequest;
use App\Interfaces\Admin\GenderRepositoryInterface;
use App\Models\Gender;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    protected $client;

    public function __construct(GenderRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Gender $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Gender $obj, GenderStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Gender $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Gender $obj, GenderUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Gender $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Gender $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
