<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\PortfolioCategoryRepositoryInterface;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;

class PortfolioCategoryController extends Controller
{
    protected $client;

    public function __construct(PortfolioCategoryRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(PortfolioCategory $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(PortfolioCategory $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(PortfolioCategory $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
