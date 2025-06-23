<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Website\WebsiteStoreRequest;
use App\Http\Requests\Customer\Website\WebsiteUpdateRequest;
use App\Interfaces\Customer\WebsiteRepositoryInterface;
use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    protected $client;

    public function __construct(WebsiteRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Website $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Website $obj, WebsiteStoreRequest $request)
    {
        return $this->client->store($obj, $request->all());
    }

    public function update(Website $obj, WebsiteUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->all(), $id);
    }

    public function show(Website $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function destroy(Website $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Website $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
