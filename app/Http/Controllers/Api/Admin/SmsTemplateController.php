<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SmsTemplate\SmsTemplateStoreRequest;
use App\Http\Requests\Admin\SmsTemplate\SmsTemplateUpdateRequest;
use App\Interfaces\Admin\SmsTemplateRepositoryInterface;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    protected $client;

    public function __construct(SmsTemplateRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(SmsTemplate $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(SmsTemplate $obj, SmsTemplateStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function update(SmsTemplate $obj, SmsTemplateUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function show(SmsTemplate $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function destroy(SmsTemplate $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(SmsTemplate $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
