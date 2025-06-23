<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\PaymentGatewayRepositoryInterface;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    protected $client;

    public function __construct(PaymentGatewayRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(PaymentGateway $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(PaymentGateway $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(PaymentGateway $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
