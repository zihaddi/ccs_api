<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\CustomerReviewRepositoryInterface;
use App\Models\CustomerReview;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    protected $client;

    public function __construct(CustomerReviewRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(CustomerReview $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(CustomerReview $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(CustomerReview $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
