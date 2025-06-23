<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\PartnerRepositoryInterface;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    protected $client;

    public function __construct(PartnerRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Partner $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Partner $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Partner $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
