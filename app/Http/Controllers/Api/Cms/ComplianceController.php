<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\ComplianceRepositoryInterface;
use App\Models\Compliance;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    protected $client;

    public function __construct(ComplianceRepositoryInterface $client)
    {
        $this->client = $client;
    }


    public function index(Compliance $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Compliance $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Compliance $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
