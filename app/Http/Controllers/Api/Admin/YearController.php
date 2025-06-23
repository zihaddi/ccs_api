<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Year\StoreYearRequest;
use App\Http\Requests\Admin\Year\UpdateYearRequest;
use App\Interfaces\Admin\YearRepositoryInterface;
use App\Models\Year;
use Illuminate\Http\Request;

class YearController extends Controller
{
    protected $client;

    public function __construct(YearRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Year $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Year $obj, StoreYearRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Year $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Year $obj, UpdateYearRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Year $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Year $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
