<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PortfolioCategory\PortfolioCategoryStoreRequest;
use App\Http\Requests\Admin\PortfolioCategory\PortfolioCategoryUpdateRequest;
use App\Interfaces\Admin\PortfolioCategoryRepositoryInterface;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;

class PortfolioCategoryController extends Controller
{
    protected $client;

    public function __construct(PortfolioCategoryRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(PortfolioCategory $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(PortfolioCategory $obj, PortfolioCategoryStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(PortfolioCategory $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(PortfolioCategory $obj, PortfolioCategoryUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(PortfolioCategory $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(PortfolioCategory $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
