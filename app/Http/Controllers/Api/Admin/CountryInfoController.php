<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryInfo\CountryInfoStoreRequest;
use App\Http\Requests\Admin\CountryInfo\CountryInfoUpdateRequest;
use App\Interfaces\Admin\CountryInfoRepositoryInterface;
use App\Models\CountryInfo;
use Illuminate\Http\Request;

class CountryInfoController extends Controller
{
    protected $client;

    public function __construct(CountryInfoRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(CountryInfo $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(CountryInfo $obj, CountryInfoStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(CountryInfo $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(CountryInfo $obj, CountryInfoUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(CountryInfo $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(CountryInfo $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
