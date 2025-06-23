<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SocialLink\SocialLinkStoreRequest;
use App\Http\Requests\Admin\SocialLink\SocialLinkUpdateRequest;
use App\Interfaces\Admin\SocialLinkRepositoryInterface;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    protected $client;

    public function __construct(SocialLinkRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(SocialLink $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(SocialLink $obj, SocialLinkStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function update(SocialLink $obj, SocialLinkUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function show(SocialLink $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function destroy(SocialLink $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(SocialLink $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
