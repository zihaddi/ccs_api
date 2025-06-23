<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\News\NewsStoreRequest;
use App\Http\Requests\Admin\News\NewsUpdateRequest;
use App\Interfaces\Admin\NewsRepositoryInterface;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    protected $client;

    public function __construct(NewsRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(News $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(News $obj, NewsStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(News $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(News $obj, NewsUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(News $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(News $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
