<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\FaqRepositoryInterface;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    protected $client;

    public function __construct(FaqRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Faq $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(Faq $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(Faq $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
