<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Currency\CurrencyStoreRequest;
use App\Http\Requests\Admin\Currency\CurrencyUpdateRequest;
use App\Interfaces\Admin\CurrencyRepositoryInterface;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $client;

    public function __construct(CurrencyRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Currency $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Currency $obj, CurrencyStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Currency $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Currency $obj, CurrencyUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Currency $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Currency $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
