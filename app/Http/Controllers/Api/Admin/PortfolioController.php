<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Portfolio\PortfolioStoreRequest;
use App\Http\Requests\Admin\Portfolio\PortfolioUpdateRequest;
use App\Interfaces\Admin\PortfolioRepositoryInterface;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    protected $client;

    public function __construct(PortfolioRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Portfolio $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Portfolio $obj, PortfolioStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Portfolio $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Portfolio $obj, PortfolioUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Portfolio $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Portfolio $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
