<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrustedBrand\TrustedBrandStoreRequest;
use App\Http\Requests\Admin\TrustedBrand\TrustedBrandUpdateRequest;
use App\Interfaces\Admin\TrustedBrandRepositoryInterface;
use App\Models\TrustedBrand;
use Illuminate\Http\Request;

class TrustedBrandController extends Controller
{
    protected $client;

    public function __construct(TrustedBrandRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(TrustedBrand $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(TrustedBrand $obj, TrustedBrandStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(TrustedBrand $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(TrustedBrand $obj, TrustedBrandUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(TrustedBrand $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(TrustedBrand $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
