<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Page\PageStoreRequest;
use App\Http\Requests\Admin\Page\PageUpdateRequest;
use App\Interfaces\Admin\PageRepositoryInterface;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected $client;

    public function __construct(PageRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Page $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Page $obj, PageStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Page $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Page $obj, PageUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Page $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Page $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
