<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\LanguageRepositoryInterface;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected $client;

    public function __construct(LanguageRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Language $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Language $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Language $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
