<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Faq\FaqStoreRequest;
use App\Http\Requests\Admin\Faq\FaqUpdateRequest;
use App\Interfaces\Admin\FaqRepositoryInterface;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    protected $client;

    public function __construct(FaqRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Faq $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Faq $obj, FaqStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Faq $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Faq $obj, FaqUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Faq $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Faq $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
