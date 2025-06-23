<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\YearRepositoryInterface;
use App\Models\Year;
use Illuminate\Http\Request;

class YearController extends Controller
{
    protected $client;

    public function __construct(YearRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Year $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Year $obj, $id)
    {
        return $this->client->show($obj, $id);
    }
}
