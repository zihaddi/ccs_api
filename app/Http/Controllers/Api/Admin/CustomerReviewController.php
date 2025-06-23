<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerReview\CustomerReviewStoreRequest;
use App\Http\Requests\Admin\CustomerReview\CustomerReviewUpdateRequest;
use App\Interfaces\Admin\CustomerReviewRepositoryInterface;
use App\Models\CustomerReview;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    protected $client;

    public function __construct(CustomerReviewRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(CustomerReview $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(CustomerReview $obj, CustomerReviewStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(CustomerReview $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(CustomerReview $obj, CustomerReviewUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(CustomerReview $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(CustomerReview $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
