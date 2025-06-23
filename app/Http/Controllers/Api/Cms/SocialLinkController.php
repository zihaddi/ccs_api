<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\SocialLinkRepositoryInterface;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    protected $client;

    public function __construct(SocialLinkRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(SocialLink $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(SocialLink $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(SocialLink $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
