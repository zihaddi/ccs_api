<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\TagStoreRequest;
use App\Http\Requests\Admin\Tag\TagUpdateRequest;
use App\Interfaces\Admin\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    protected $client;

    public function __construct(TagRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Tag $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Tag $obj, TagStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function update(Tag $obj, TagUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function show(Tag $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function destroy(Tag $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Tag $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
