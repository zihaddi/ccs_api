<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmailTemplate\EmailTemplateStoreRequest;
use App\Http\Requests\Admin\EmailTemplate\EmailTemplateUpdateRequest;
use App\Interfaces\Admin\EmailTemplateRepositoryInterface;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    protected $client;

    public function __construct(EmailTemplateRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(EmailTemplate $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(EmailTemplate $obj, EmailTemplateStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(EmailTemplate $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(EmailTemplate $obj, EmailTemplateUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(EmailTemplate $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(EmailTemplate $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
