<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tutorial\TutorialStoreRequest;
use App\Http\Requests\Admin\Tutorial\TutorialUpdateRequest;
use App\Interfaces\Admin\TutorialRepositoryInterface;
use App\Models\Tutorial;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    protected $client;

    public function __construct(TutorialRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Tutorial $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Tutorial $obj, TutorialStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Tutorial $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Tutorial $obj, TutorialUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Tutorial $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Tutorial $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
