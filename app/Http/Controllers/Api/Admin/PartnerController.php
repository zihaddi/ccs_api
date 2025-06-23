<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Partner\PartnerStoreRequest;
use App\Http\Requests\Admin\Partner\PartnerUpdateRequest;
use App\Interfaces\Admin\PartnerRepositoryInterface;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    protected $client;

    public function __construct(PartnerRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Partner $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Partner $obj, PartnerStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Partner $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Partner $obj, PartnerUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Partner $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Partner $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
