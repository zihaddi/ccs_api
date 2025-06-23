<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Language\LanguageStoreRequest;
use App\Http\Requests\Admin\Language\LanguageUpdateRequest;
use App\Interfaces\Admin\LanguageRepositoryInterface;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected $client;

    public function __construct(LanguageRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Language $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Language $obj, LanguageStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Language $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Language $obj, LanguageUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Language $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Language $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
