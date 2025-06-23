<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentGateway\PaymentGatewayStoreRequest;
use App\Http\Requests\Admin\PaymentGateway\PaymentGatewayUpdateRequest;
use App\Interfaces\Admin\PaymentGatewayRepositoryInterface;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    protected $client;

    public function __construct(PaymentGatewayRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(PaymentGateway $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(PaymentGateway $obj, PaymentGatewayStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(PaymentGateway $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(PaymentGateway $obj, PaymentGatewayUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(PaymentGateway $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(PaymentGateway $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
