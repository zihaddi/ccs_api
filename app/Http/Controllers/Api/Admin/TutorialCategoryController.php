<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TutorialCategory\TutorialCategoryStoreRequest;
use App\Http\Requests\Admin\TutorialCategory\TutorialCategoryUpdateRequest;
use App\Interfaces\Admin\TutorialCategoryRepositoryInterface;
use App\Models\TutorialCategory;
use Illuminate\Http\Request;

class TutorialCategoryController extends Controller
{
    protected $client;

    public function __construct(TutorialCategoryRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(TutorialCategory $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(TutorialCategory $obj, TutorialCategoryStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function update(TutorialCategory $obj, TutorialCategoryUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function show(TutorialCategory $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function destroy(TutorialCategory $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(TutorialCategory $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
