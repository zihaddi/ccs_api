<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Admin\ContactRepositoryInterface;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    protected $client;

    public function __construct(ContactRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Contact $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Contact $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function destroy(Contact $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Contact $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
