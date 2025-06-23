<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\ReleaseNoteRepositoryInterface;
use App\Models\ReleaseNote;
use Illuminate\Http\Request;

class ReleaseNoteController extends Controller
{
    protected $client;

    public function __construct(ReleaseNoteRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(ReleaseNote $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }
    public function show(ReleaseNote $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(ReleaseNote $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
