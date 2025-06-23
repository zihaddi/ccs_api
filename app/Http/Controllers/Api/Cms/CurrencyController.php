<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\CurrencyRepositoryInterface;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $client;

    public function __construct(CurrencyRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Currency $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Currency $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Currency $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
