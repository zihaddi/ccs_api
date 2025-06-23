<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqCategory\FaqCategoryStoreRequest;
use App\Http\Requests\Admin\FaqCategory\FaqCategoryUpdateRequest;
use App\Interfaces\Admin\FaqCategoryRepositoryInterface;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqCategoryController extends Controller
{
    protected $client;

    public function __construct(FaqCategoryRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(FaqCategory $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(FaqCategory $obj, FaqCategoryStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(FaqCategory $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(FaqCategory $obj, FaqCategoryUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(FaqCategory $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(FaqCategory $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
