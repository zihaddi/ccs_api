<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReleaseNote\ReleaseNoteStoreRequest;
use App\Http\Requests\Admin\ReleaseNote\ReleaseNoteUpdateRequest;
use App\Interfaces\Admin\ReleaseNoteRepositoryInterface;
use App\Models\ReleaseNote;
use Illuminate\Http\Request;

class ReleaseNoteController extends Controller
{
    protected $client;

    public function __construct(ReleaseNoteRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(ReleaseNote $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(ReleaseNote $obj, ReleaseNoteStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(ReleaseNote $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(ReleaseNote $obj, ReleaseNoteUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(ReleaseNote $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(ReleaseNote $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
