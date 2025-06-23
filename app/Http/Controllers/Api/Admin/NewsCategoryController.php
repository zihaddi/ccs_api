<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsCategory\NewsCategoryStoreRequest;
use App\Http\Requests\Admin\NewsCategory\NewsCategoryUpdateRequest;
use App\Interfaces\Admin\NewsCategoryRepositoryInterface;
use App\Models\NewsCategory;
use Illuminate\Http\Request;

class NewsCategoryController extends Controller
{
    protected $client;

    public function __construct(NewsCategoryRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(NewsCategory $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(NewsCategory $obj, NewsCategoryStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(NewsCategory $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(NewsCategory $obj, NewsCategoryUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(NewsCategory $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(NewsCategory $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
