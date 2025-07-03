<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TvProgram\TvProgramStoreRequest;
use App\Http\Requests\Admin\TvProgram\TvProgramUpdateRequest;
use App\Interfaces\Admin\TvProgramRepositoryInterface;
use App\Models\TvProgram;
use Illuminate\Http\Request;

class TvProgramController extends Controller
{
    protected $client;

    public function __construct(TvProgramRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(TvProgram $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(TvProgram $obj, TvProgramStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(TvProgram $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(TvProgram $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }

    public function update(TvProgram $obj, TvProgramUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(TvProgram $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(TvProgram $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
