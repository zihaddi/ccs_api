<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\TrustedBrandRepositoryInterface;
use App\Models\TrustedBrand;
use Illuminate\Http\Request;

class TrustedBrandController extends Controller
{
    protected $client;

    public function __construct(TrustedBrandRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(TrustedBrand $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(TrustedBrand $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(TrustedBrand $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
