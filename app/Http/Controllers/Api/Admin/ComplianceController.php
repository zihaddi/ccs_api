<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Compliance\ComplianceStoreRequest;
use App\Http\Requests\Admin\Compliance\ComplianceUpdateRequest;
use App\Interfaces\Admin\ComplianceRepositoryInterface;
use App\Models\Compliance;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    protected $client;

    public function __construct(ComplianceRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Compliance $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Compliance $obj, ComplianceStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Compliance $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Compliance $obj, ComplianceUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Compliance $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Compliance $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
